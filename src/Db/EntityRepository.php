<?php
namespace Common\Db;

use Doctrine\ORM\EntityRepository as DoctrineEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class EntityRepository extends DoctrineEntityRepository
{
	const DEFAULT_OFFSET   = 0;
	const DEFAULT_LIMIT    = PHP_INT_MAX;
	const DEFAULT_DISTINCT = false;

	/**
	 * @return Entity[]
	 */
	public function filter(
		FilterChain $filterChain,
		?OrderChain $orderChain = null,
		?int $offset = self::DEFAULT_OFFSET,
		?int $limit = self::DEFAULT_LIMIT,
		?bool $distinct = self::DEFAULT_DISTINCT
	): array
	{
		$queryBuilder = $this->buildQueryBuilder($filterChain, $orderChain, $offset, $limit, $distinct);

		return $queryBuilder
			->getQuery()
			->getResult();
	}

	/**
	 * @return mixed[]
	 */
	public function filterAndReturnIds(
		FilterChain $filterChain,
		?OrderChain $orderChain = null,
		?int $offset = self::DEFAULT_OFFSET,
		?int $limit = self::DEFAULT_LIMIT,
		?bool $distinct = self::DEFAULT_DISTINCT
	): array
	{
		$queryBuilder = $this->buildQueryBuilder($filterChain, $orderChain, $offset, $limit, $distinct);

		$queryBuilder->select('t.id AS id');

		return array_map(
			fn(array $item) => $item['id'],
			$queryBuilder
				->getQuery()
				->getScalarResult()
		);
	}

	/**
	 * @throws NonUniqueResultException
	 * @throws NoResultException
	 */
	public function countWithFilter(FilterChain $filterChain = null, ?bool $distinct = false): int
	{
		$identifiers = $this
			->getClassMetadata()
			->getIdentifier();

		$queryBuilder = $this
			->createQueryBuilder('t')
			->select('COUNT(' . ($distinct
					? 'DISTINCT'
					: '') . ' t.' . $identifiers[0] . ')');

		if ($filterChain)
		{
			foreach ($filterChain->getFilters() as $filter)
			{
				$filter->addClause($queryBuilder);
			}
		}

		return $queryBuilder
			->getQuery()
			->getSingleScalarResult();
	}

	private function buildQueryBuilder(
		FilterChain $filterChain,
		?OrderChain $orderChain = null,
		?int $offset = self::DEFAULT_OFFSET,
		?int $limit = self::DEFAULT_LIMIT,
		?bool $distinct = self::DEFAULT_DISTINCT
	): QueryBuilder
	{
		$queryBuilder = $this->createQueryBuilder('t');

		if ($distinct)
		{
			$queryBuilder->distinct();
		}

		foreach ($filterChain->getFilters() as $filter)
		{
			$filter->addClause($queryBuilder);
		}

		if ($orderChain)
		{
			foreach ($orderChain->getOrders() as $order)
			{
				$order->addOrder($queryBuilder);
			}
		}

		if ($offset === null)
		{
			$offset = self::DEFAULT_OFFSET;
		}

		if ($limit === null)
		{
			$limit = self::DEFAULT_LIMIT;
		}

		$queryBuilder->setFirstResult($offset);
		$queryBuilder->setMaxResults($limit);

		return $queryBuilder;
	}
}
