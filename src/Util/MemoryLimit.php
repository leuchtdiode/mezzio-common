<?php
declare(strict_types=1);

namespace Common\Util;

class MemoryLimit
{
	public static function megabyte(int $mb, bool $raiseOnly = true): void
	{
		self::setOrRaise($mb . 'M', $raiseOnly);
	}

	public static function gigabyte(int $gb, bool $raiseOnly = true): void
	{
		self::setOrRaise($gb . 'G', $raiseOnly);
	}

	private static function setOrRaise(string $target, bool $raiseOnly): void
	{
		if ($raiseOnly)
		{
			self::raiseTo($target);
		}
		else
		{
			ini_set('memory_limit', $target);
		}
	}

	/**
	 * Only ever grows the budget. A long running job sets its limit once up front, a helper it
	 * calls deeper down must not shrink it mid run - lowering memory_limit frees nothing, it only
	 * makes the next allocation fatal.
	 */
	private static function raiseTo(string $target): void
	{
		$current = ini_get('memory_limit');

		if ($current !== false)
		{
			$currentBytes = self::toBytes($current);

			// -1 is unlimited, anything else is replaced only when the new value is larger
			if ($currentBytes < 0 || $currentBytes >= self::toBytes($target))
			{
				return;
			}
		}

		ini_set('memory_limit', $target);
	}

	/**
	 * Resolves PHP shorthand notation (128M, 2G, 1024K, plain bytes, -1) to bytes.
	 */
	private static function toBytes(string $value): int
	{
		$value  = trim($value);
		$number = (int)$value;

		return match (strtolower(substr($value, -1)))
		{
			'g' => $number * 1024 * 1024 * 1024,
			'm' => $number * 1024 * 1024,
			'k' => $number * 1024,
			default => $number,
		};
	}
}
