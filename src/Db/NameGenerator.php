<?php
declare(strict_types=1);

namespace Common\Db;

use Doctrine\ORM\QueryBuilder;
use WeakMap;

/**
 * Creates parameter names and aliases which are unique within a single query but stable
 * across queries of the same shape.
 *
 * Random names (uniqid) produced a different DQL string on every single execution, so
 * Doctrines query cache stored a new ParserResult for every request and grew without bounds.
 *
 * The counter is bound to the (outer) QueryBuilder instance and is released together with it,
 * so nothing is kept alive by this class.
 */
class NameGenerator
{
	/**
	 * @var WeakMap<QueryBuilder, int>|null
	 */
	private static ?WeakMap $counters = null;

	/**
	 * Always pass the outermost QueryBuilder, aliases have to be unique within the whole DQL,
	 * subqueries included.
	 */
	public static function next(QueryBuilder $queryBuilder, string $prefix): string
	{
		$counters = self::$counters ??= new WeakMap();

		$counter = ($counters[$queryBuilder] ?? 0) + 1;

		$counters[$queryBuilder] = $counter;

		return $prefix . $counter;
	}
}
