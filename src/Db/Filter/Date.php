<?php
namespace Common\Db\Filter;

use Common\Db\Filter;
use Common\Db\NameGenerator;
use Common\Util\ClassUtil;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Exception;
use RuntimeException;

abstract class Date implements Filter
{
	const IS      = 'is';
	const IN_DAYS = 'in_days';
	const MAX     = 'max';
	const MIN     = 'min';
	const BEFORE  = 'before';
	const AFTER   = 'after';
	const MODULO  = 'modulo';

	protected DateTime $value;

	protected string $mode;

	protected function getColumn(): string
	{
		return 't.' . lcfirst(ClassUtil::getShortName($this));
	}

	private function __construct(
		DateTime $value,
		string $mode = self::IN_DAYS
	)
	{
		$this->value = $value;
		$this->mode  = $mode;
	}

	protected function isDateTime(): bool
	{
		return true;
	}

	/**
	 * @throws Exception
	 */
	public static function inDays(int $days): static
	{
		$date = new DateTime();
		$date->modify(
			sprintf(
				'%s%d days',
				$days < 0
					? '-'
					: '+',
				abs($days)
			)
		);

		return new static($date, self::IN_DAYS);
	}

	public static function is(DateTime $date): static
	{
		return new static($date, self::IS);
	}

	public static function min(DateTime $date): static
	{
		return new static($date, self::MIN);
	}

	public static function max(DateTime $date): static
	{
		return new static($date, self::MAX);
	}

	public static function before(DateTime $date): static
	{
		return new static($date, self::BEFORE);
	}

	public static function after(DateTime $date): static
	{
		return new static($date, self::AFTER);
	}

	/**
	 * @throws Exception
	 */
	public static function modulo(int $days): static
	{
		$date = new DateTime();
		$date->modify(
			sprintf(
				'%s%d days',
				$days < 0
					? '-'
					: '+',
				abs($days)
			)
		);

		return new static($date, self::MODULO);
	}

	public function addClause(QueryBuilder $queryBuilder): void
	{
		$exp = $queryBuilder->expr();

		$format = $this->isDateTime()
			? 'Y-m-d H:i:s'
			: 'Y-m-d';

		// values are bound as parameters, inlining them would create a new DQL string
		// for every value and therefore a new query cache entry
		$parameterName = NameGenerator::next($queryBuilder, 'p');

		$placeholder = ':' . $parameterName;

		switch ($this->mode)
		{
			case self::IS:

				$value = $this->value->format($format);

				$condition = $exp->eq(
					$this->getColumn(),
					$placeholder
				);

				break;

			case self::IN_DAYS:

				$value = $this->value->format('Y-m-d');

				$condition = $exp->eq(
					"DATE_FORMAT({$this->getColumn()}, '%Y-%m-%d')",
					$placeholder
				);

				break;

			case self::MIN:

				$value = $this->value->format($format);

				$condition = $exp->gte(
					$this->getColumn(),
					$placeholder
				);

				break;

			case self::MAX:

				$value = $this->value->format($format);

				$condition = $exp->lte(
					$this->getColumn(),
					$placeholder
				);

				break;

			case self::BEFORE:

				$value = $this->value->format($format);

				$condition = $exp->lt(
					$this->getColumn(),
					$placeholder
				);

				break;

			case self::AFTER:

				$value = $this->value->format($format);

				$condition = $exp->gt(
					$this->getColumn(),
					$placeholder
				);

				break;

			case self::MODULO:

				$value = $this->value->format('m-d');

				$condition = $exp->eq(
					"DATE_FORMAT({$this->getColumn()}, '%m-%d')",
					$placeholder
				);

				break;

			default:
				throw new RuntimeException('invalid mode in string filter');
		}

		$queryBuilder
			->andWhere($condition)
			->setParameter($parameterName, $value);
	}
}