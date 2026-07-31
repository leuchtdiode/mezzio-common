<?php

namespace CommonTest\Db;

use Common\Db\NameGenerator;
use CommonTest\Base;
use CommonTest\EntityManagerMockTrait;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use ReflectionProperty;
use WeakMap;

#[AllowMockObjectsWithoutExpectations]
class NameGeneratorTest extends Base
{
	use EntityManagerMockTrait;

	public function test_names_are_sequential_within_one_query_builder(): void
	{
		$queryBuilder = new QueryBuilder($this->getEntityManagerMock());

		$this->assertEquals('p1', NameGenerator::next($queryBuilder, 'p'));
		$this->assertEquals('p2', NameGenerator::next($queryBuilder, 'p'));
		$this->assertEquals('s3', NameGenerator::next($queryBuilder, 's'));
		$this->assertEquals('vp4', NameGenerator::next($queryBuilder, 'vp'));
	}

	public function test_counter_is_isolated_per_query_builder(): void
	{
		$first  = new QueryBuilder($this->getEntityManagerMock());
		$second = new QueryBuilder($this->getEntityManagerMock());

		$this->assertEquals('p1', NameGenerator::next($first, 'p'));
		$this->assertEquals('p1', NameGenerator::next($second, 'p'));
		$this->assertEquals('p2', NameGenerator::next($first, 'p'));
		$this->assertEquals('p2', NameGenerator::next($second, 'p'));
	}

	/**
	 * This is what the whole change is about, the generator must not keep anything alive.
	 */
	public function test_counter_is_released_together_with_query_builder(): void
	{
		$queryBuilder = new QueryBuilder($this->getEntityManagerMock());

		NameGenerator::next($queryBuilder, 'p');

		$countWithQueryBuilder = count($this->getCounters());

		unset($queryBuilder);

		$this->assertEquals(
			$countWithQueryBuilder - 1,
			count($this->getCounters())
		);
	}

	private function getCounters(): WeakMap
	{
		return (new ReflectionProperty(NameGenerator::class, 'counters'))
			->getValue();
	}
}
