<?php
declare(strict_types=1);

namespace Common\Util;

class MemoryLimit
{
	public static function megabyte(int $mb): void
	{
		ini_set('memory_limit', $mb . 'M');
	}

	public static function gigabyte(int $gb): void
	{
		ini_set('memory_limit', $gb . 'G');
	}
}
