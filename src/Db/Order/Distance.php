<?php
namespace Common\Db\Order;

use Common\Db\NameGenerator;
use Common\Db\Order;
use Doctrine\ORM\QueryBuilder;

abstract class Distance implements Order
{
	protected float  $latitude;
	protected float  $longitude;
	protected string $direction;

	abstract protected function getAlias(): string;

	private function __construct(float $latitude, float $longitude, string $direction)
	{
		$this->latitude  = $latitude;
		$this->longitude = $longitude;
		$this->direction = $direction;
	}

	public function getMissingDefaultDistance(): int
	{
		return 999999999;
	}

	public function isSortMissingLast(): int
	{
		return true;
	}

	public static function nearest(float $latitude, float $longitude): self
	{
		return new static($latitude, $longitude, 'ASC');
	}

	public static function widest(float $latitude, float $longitude): self
	{
		return new static($latitude, $longitude, 'DESC');
	}

	public function addOrder(QueryBuilder $queryBuilder): void
	{
		$alias = $this->getAlias();

		$distanceColumn = NameGenerator::next($queryBuilder, 'd');

		$missingDefaultDistance = $this->getMissingDefaultDistance();

		if (!$this->isSortMissingLast())
		{
			$missingDefaultDistance = $missingDefaultDistance * -1;
		}

		$queryBuilder
			->addSelect(
				sprintf(
					'COALESCE(DISTANCE(:%s, :%s, %s.latitude, %s.longitude), :%s) AS HIDDEN %s',
					$latitudeParam = NameGenerator::next($queryBuilder, 'lat'),
					$longitudeParam = NameGenerator::next($queryBuilder, 'lon'),
					$alias,
					$alias,
					$missingDefaultDistanceColumn = NameGenerator::next($queryBuilder, 'md'),
					$distanceColumn
				)
			)
			->setParameter($latitudeParam, $this->latitude)
			->setParameter($longitudeParam, $this->longitude)
			->setParameter($missingDefaultDistanceColumn, $missingDefaultDistance)
			->addOrderBy($distanceColumn, $this->direction);
	}
}