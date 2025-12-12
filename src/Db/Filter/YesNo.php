<?php
namespace Common\Db\Filter;

use Common\Db\Filter;
use Common\Util\ClassUtil;
use Doctrine\ORM\QueryBuilder;

abstract class YesNo implements Filter
{
	protected bool $value;

	protected function getColumn(): string
	{
		return 't.' . lcfirst(ClassUtil::getShortName($this));
	}

	private function __construct(bool $value)
	{
		$this->value = $value;
	}

	public static function yes(): static
	{
		return new static(true);
	}

	public static function no(): static
	{
		return new static(false);
	}

	public static function as(bool $value): static
	{
		return new static($value);
	}

	public function addClause(QueryBuilder $queryBuilder): void
	{
		$queryBuilder
			->andWhere(
				$queryBuilder->expr()->eq($this->getColumn(), $this->value ? 1 : 0)
			);
	}
}