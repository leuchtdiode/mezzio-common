<?php
declare(strict_types=1);

namespace Common\Util;

use InvalidArgumentException;

trait MemoryUsage
{
	protected function getMemoryUsage(MemoryUsageUnit $unit = MemoryUsageUnit::Megabyte): string
	{
		if ($unit === MemoryUsageUnit::Megabyte)
		{
			return memory_get_usage(true) / 1000 / 1024 . 'MB';
		}

		throw new InvalidArgumentException('Unknown unit');
	}
}
