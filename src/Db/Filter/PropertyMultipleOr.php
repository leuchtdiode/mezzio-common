<?php
declare(strict_types=1);

namespace Common\Db\Filter;

use Common\Db\Filter;
use Doctrine\ORM\QueryBuilder;
use Throwable;

class PropertyMultipleOr implements Filter
{
	/**
	 * @param Property[] $properties
	 */
	public function __construct(
		private readonly array $properties
	)
	{
	}

	/**
	 * @param Property[] $properties
	 */
	public static function filter(array $properties): static
	{
		return new static($properties);
	}

	/**
	 * @throws Throwable
	 */
	public function addClause(QueryBuilder $queryBuilder): void
	{
		$expr = $queryBuilder->expr();
		
		$orx = $expr->orX();
		
		foreach ($this->properties as $property)
		{
			$property->setAddToExpression($orx);
			$property->addClause($queryBuilder);
		}

		$queryBuilder->andWhere($orx);
	}
}
