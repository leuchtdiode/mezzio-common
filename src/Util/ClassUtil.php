<?php
declare(strict_types=1);

namespace Common\Util;

class ClassUtil
{
	public static function getShortName(object|string $class): mixed
	{
		$class = is_object($class)
			? get_class($class)
			: $class;

		return basename(str_replace('\\', '/', $class));
	}
}
