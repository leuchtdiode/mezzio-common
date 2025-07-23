<?php
namespace CommonTest\Util;

use Common\Util\StringUtil;
use CommonTest\Base;
use PHPUnit\Framework\Attributes\DataProvider;

class StringUtilStartsWithTest extends Base
{
	/**
	 * @param $string
	 * @param $startsWith
	 * @param $expectedResult
	 */
	#[DataProvider(methodName: 'startsWithSet')]
	public function test_starts_with($string, $startsWith, $expectedResult)
	{
		$this->assertEquals(
			$expectedResult,
			StringUtil::startsWith($string, $startsWith)
		);
	}

	/**
	 * @return array
	 */
	public static function startsWithSet()
	{
		return [
			[ 'test', 't', true ],
			[ 'test', 'x', false ],
			[ 'test', 'test', true ],
			[ 'test', 'testx', false ],
		];
	}
}