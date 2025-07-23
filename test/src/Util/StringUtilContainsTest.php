<?php
namespace CommonTest\Util;

use Common\Util\StringUtil;
use CommonTest\Base;
use PHPUnit\Framework\Attributes\DataProvider;

class StringUtilContainsTest extends Base
{
	/**
	 * @param $string
	 * @param $containing
	 * @param $contains
	 */
	#[DataProvider(methodName: 'containsSet')]
	public function test_contains($string, $containing, $contains)
	{
		$this->assertEquals(
			$contains,
			StringUtil::contains($string, $containing)
		);
	}

	/**
	 * @return array
	 */
	public static function containsSet()
	{
		return [
			[ 'test', 't', true ],
			[ 'test', 'test', true ],
			[ 'test', 'testx', false ],
			[ 'test', 'x', false ],
			[ 'test xxx', 'xxx', true ],
			[ 'töst', 'ö', true ],
		];
	}
}