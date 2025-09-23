<?php
declare(strict_types=1);

namespace Common\File;

class Utf8Bom
{
	public static function get(): string
	{
		return "\xEF\xBB\xBF";
	}
}
