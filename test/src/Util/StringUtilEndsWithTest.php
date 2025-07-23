<?php
namespace CommonTest\Util;

use Common\Util\StringUtil;
use CommonTest\Base;
use PHPUnit\Framework\Attributes\DataProvider;

class StringUtilEndsWithTest extends Base
{
	/**
	 * @param $string
	 * @param $endsWith
	 * @param $expectedResult
	 */
	#[DataProvider(methodName: 'startsWithSet')]
	public function test_ends_with($string, $endsWith, $expectedResult)
	{
		$this->assertEquals(
			$expectedResult,
			StringUtil::endsWith($string, $endsWith)
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
			[ 'test', 'est', true ],
			[ 'test', 'testx', false ],
		];
	}
}