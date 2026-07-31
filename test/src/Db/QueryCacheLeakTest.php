<?php

namespace CommonTest\Db;

use Common\Db\Filter;
use Common\Db\FilterChain;
use Common\Db\NameGenerator;
use Common\Db\Filter\Property;
use Common\Db\Filter\Property\EqualsParams;
use Common\Db\Filter\Property\PropertyChain;
use CommonTest\Base;
use CommonTest\Db\Entity\LeakEntity;
use CommonTest\Db\Filter\AmountFilter;
use CommonTest\Db\Filter\CreatedFilter;
use CommonTest\Db\Filter\NameFilter;
use CommonTest\Db\Filter\SearchFilter;
use CommonTest\Db\Functions\DateFormat;
use DateTime;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Query;
use ReflectionProperty;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use WeakMap;

/**
 * Doctrine keeps the parsed result of every distinct DQL string in the query cache. As long as
 * the filters produce a stable DQL, thousands of executions share a single cache entry. Random
 * parameter names or inlined values would add one ParserResult per execution, which is exactly
 * the leak this guards against.
 */
class QueryCacheLeakTest extends Base
{
	private const int ITERATIONS = 2000;

	private ArrayAdapter $queryCache;

	private EntityManager $entityManager;

	protected function setUp(): void
	{
		parent::setUp();

		$configuration = ORMSetup::createAttributeMetadataConfiguration(
			[ __DIR__ . '/Entity' ],
			true
		);

		$this->queryCache = new ArrayAdapter(0, false);

		$configuration->setQueryCache($this->queryCache);
		$configuration->setMetadataCache(new ArrayAdapter(0, false));
		$configuration->enableNativeLazyObjects(true);
		$configuration->addCustomStringFunction('DATE_FORMAT', DateFormat::class);

		$this->entityManager = new EntityManager(
			DriverManager::getConnection(
				[
					'driver' => 'pdo_sqlite',
					'memory' => true,
				],
				$configuration
			),
			$configuration
		);
	}

	public function test_thousands_of_queries_share_a_single_query_cache_entry(): void
	{
		for ($i = 0; $i < self::ITERATIONS; $i++)
		{
			$this->buildQuery($i)->getSQL();
		}

		$this->assertCount(
			1,
			$this->queryCache->getValues(),
			'every execution added its own ParserResult to the query cache'
		);
	}

	public function test_memory_does_not_grow_with_the_number_of_queries(): void
	{
		// warm up, the first executions fill metadata cache, parser and hydration caches
		for ($i = 0; $i < 100; $i++)
		{
			$this->buildQuery($i)->getSQL();
		}

		gc_collect_cycles();

		$memoryAfterWarmUp = memory_get_usage();

		for ($i = 0; $i < self::ITERATIONS; $i++)
		{
			$this->buildQuery($i)->getSQL();
		}

		gc_collect_cycles();

		$growth = memory_get_usage() - $memoryAfterWarmUp;

		$this->assertLessThan(
			1024 * 1024,
			$growth,
			sprintf(
				'%d queries grew memory by %d bytes',
				self::ITERATIONS,
				$growth
			)
		);
	}

	public function test_name_generator_keeps_no_state_after_the_queries_are_gone(): void
	{
		for ($i = 0; $i < self::ITERATIONS; $i++)
		{
			$this->buildQuery($i)->getSQL();
		}

		gc_collect_cycles();

		$this->assertCount(
			0,
			$this->getNameGeneratorCounters(),
			'the name generator still references query builders'
		);
	}

	private function buildQuery(int $iteration): Query
	{
		$queryBuilder = $this->entityManager
			->createQueryBuilder()
			->select('t')
			->from(LeakEntity::class, 't');

		foreach ($this->getFilterChain($iteration)->getFilters() as $filter)
		{
			$filter->addClause($queryBuilder);
		}

		return $queryBuilder->getQuery();
	}

	/**
	 * Same chain on every iteration, but with different values. Those values must not end up in
	 * the DQL.
	 */
	private function getFilterChain(int $iteration): FilterChain
	{
		return FilterChain::create()
			->addFilter(NameFilter::is('name' . $iteration))
			->addFilter(NameFilter::in([ 'a' . $iteration, 'b' . $iteration ]))
			->addFilter(AmountFilter::greaterThan($iteration))
			->addFilter(CreatedFilter::min(new DateTime('2026-07-31 12:13:14 +' . $iteration . ' days')))
			->addFilter(CreatedFilter::inDays($iteration))
			->addFilter(SearchFilter::search('term' . $iteration . ' other' . $iteration))
			->addFilter($this->getPropertyFilter($iteration))
			->addFilter(
				Filter\PropertyMultipleOr::filter(
					[
						$this->getPropertyFilter($iteration),
						$this->getPropertyFilter($iteration),
					]
				)
			);
	}

	private function getPropertyFilter(int $iteration): Property
	{
		return Property::filter(
			EqualsParams::create()
				->setValues([ 'value' . $iteration ])
				->setPropertyChain(PropertyChain::buildFromString('company.name'))
		);
	}

	private function getNameGeneratorCounters(): WeakMap
	{
		return (new ReflectionProperty(NameGenerator::class, 'counters'))
			->getValue();
	}
}
