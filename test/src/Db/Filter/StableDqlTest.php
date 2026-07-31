<?php

namespace CommonTest\Db\Filter;

use Common\Db\Filter;
use Common\Db\Filter\Distance;
use Common\Db\Filter\Property;
use Common\Db\Filter\Property\EqualsParams;
use Common\Db\Filter\Property\PropertyChain;
use CommonTest\Base;
use CommonTest\Db\Order\DistanceOrder;
use CommonTest\EntityManagerMockTrait;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Doctrines query cache is keyed by the DQL string, so a filter has to produce the same DQL
 * on every execution. Random parameter names or inlined values would create a new cache entry
 * per request and let the cache grow without bounds.
 */
#[AllowMockObjectsWithoutExpectations]
class StableDqlTest extends Base
{
	use EntityManagerMockTrait;

	#[DataProvider(methodName: 'filters')]
	public function test_dql_is_stable_across_query_builders(callable $filterFactory): void
	{
		$this->assertEquals(
			$this->buildDql($filterFactory()),
			$this->buildDql($filterFactory())
		);
	}

	#[DataProvider(methodName: 'filters')]
	public function test_dql_contains_no_random_names(callable $filterFactory): void
	{
		$this->assertDoesNotMatchRegularExpression(
			'/[a-f0-9]{13}/',
			$this->buildDql($filterFactory())
		);
	}

	public function test_generated_names_do_not_collide_within_one_query(): void
	{
		$queryBuilder = $this->getQueryBuilder();

		NameFilter::is('first')->addClause($queryBuilder);
		NameFilter::is('second')->addClause($queryBuilder);

		$this->assertEquals(
			'SELECT t FROM Entity t WHERE t.name = :p1 AND t.name = :p2',
			$queryBuilder->getDQL()
		);

		$this->assertEquals(
			[
				'p1' => 'first',
				'p2' => 'second',
			],
			$this->getParameters($queryBuilder)
		);
	}

	public function test_property_filter_creates_unique_aliases_per_query(): void
	{
		$queryBuilder = $this->getQueryBuilder();

		$this->getPropertyFilter()->addClause($queryBuilder);
		$this->getPropertyFilter()->addClause($queryBuilder);

		$dql = $queryBuilder->getDQL();

		preg_match_all('/(?:FROM Entity|LEFT JOIN \S+) (s\d+)/', $dql, $matches);

		// both subqueries live in the same DQL scope, so their aliases must not repeat
		$this->assertEquals(
			[ 's1', 's2', 's4', 's5' ],
			$matches[1],
			'declared aliases in ' . $dql
		);

		$this->assertEquals(
			[ 'vp3', 'vp6' ],
			array_keys($this->getParameters($queryBuilder))
		);
	}

	public function test_date_filter_binds_value_instead_of_inlining_it(): void
	{
		$queryBuilder = $this->getQueryBuilder();

		CreatedFilter::min(new DateTime('2026-07-31 12:13:14'))
			->addClause($queryBuilder);

		$this->assertEquals(
			'SELECT t FROM Entity t WHERE t.created >= :p1',
			$queryBuilder->getDQL()
		);

		$this->assertEquals(
			[ 'p1' => '2026-07-31 12:13:14' ],
			$this->getParameters($queryBuilder)
		);
	}

	public function test_date_filter_dql_does_not_depend_on_the_value(): void
	{
		$this->assertEquals(
			$this->buildDql(CreatedFilter::min(new DateTime('2026-07-31 12:13:14'))),
			$this->buildDql(CreatedFilter::min(new DateTime('2019-01-01 00:00:00')))
		);
	}

	public function test_distance_filter_dql_does_not_depend_on_the_kilometers(): void
	{
		$this->assertEquals(
			$this->buildDql($this->getDistanceFilter(10.0)),
			$this->buildDql($this->getDistanceFilter(250.5))
		);
	}

	public function test_distance_order_dql_is_stable(): void
	{
		$first  = $this->getQueryBuilder();
		$second = $this->getQueryBuilder();

		DistanceOrder::nearest(48.2, 16.3)->addOrder($first);
		DistanceOrder::nearest(11.1, 22.2)->addOrder($second);

		$this->assertEquals($first->getDQL(), $second->getDQL());

		$this->assertEquals(
			'SELECT t, COALESCE(DISTANCE(:lat2, :lon3, t.latitude, t.longitude), :md4) AS HIDDEN d1'
			. ' FROM Entity t ORDER BY d1 ASC',
			$first->getDQL()
		);
	}

	public static function filters(): array
	{
		return [
			'equals'          => [ fn() => NameFilter::is('value') ],
			'equalsIn'        => [ fn() => NameFilter::in([ 'a', 'b' ]) ],
			'equalsIsNull'    => [ fn() => NameFilter::isNull() ],
			'number'          => [ fn() => AmountFilter::greaterThan(5) ],
			'generic'         => [ fn() => SearchFilter::search('two words') ],
			'date'            => [ fn() => CreatedFilter::min(new DateTime('2026-07-31 12:13:14')) ],
			'dateInDays'      => [ fn() => CreatedFilter::inDays(3) ],
			'property'        => [ fn() => self::createPropertyFilter() ],
			'propertyMulti'   => [
				fn() => Filter\PropertyMultipleOr::filter(
					[
						self::createPropertyFilter(),
						self::createPropertyFilter(),
					]
				),
			],
		];
	}

	private function getPropertyFilter(): Property
	{
		return self::createPropertyFilter();
	}

	private static function createPropertyFilter(): Property
	{
		return Property::filter(
			EqualsParams::create()
				->setValues([ 'a', 'b' ])
				->setPropertyChain(PropertyChain::buildFromString('company.name'))
		);
	}

	private function getDistanceFilter(float $kilometers): Distance
	{
		return Distance::filter(
			Distance\FilterParams::create()
				->setType(Distance::TYPE_MAX)
				->setSourceLatitude(Distance\ColumnOrValue::create()->setValue(48.2))
				->setSourceLongitude(Distance\ColumnOrValue::create()->setValue(16.3))
				->setDestinationLatitude(Distance\ColumnOrValue::create()->setColumn('t.latitude'))
				->setDestinationLongitude(Distance\ColumnOrValue::create()->setColumn('t.longitude'))
				->setKilometers($kilometers)
		);
	}

	private function buildDql(Filter $filter): string
	{
		$queryBuilder = $this->getQueryBuilder();

		$filter->addClause($queryBuilder);

		return $queryBuilder->getDQL();
	}

	private function getQueryBuilder(): QueryBuilder
	{
		return (new QueryBuilder($this->getEntityManagerMock()))
			->select('t')
			->from('Entity', 't');
	}

	/**
	 * @return array<string, mixed>
	 */
	private function getParameters(QueryBuilder $queryBuilder): array
	{
		$parameters = [];

		foreach ($queryBuilder->getParameters() as $parameter)
		{
			$parameters[$parameter->getName()] = $parameter->getValue();
		}

		return $parameters;
	}
}
