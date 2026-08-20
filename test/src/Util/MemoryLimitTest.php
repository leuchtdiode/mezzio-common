<?php
declare(strict_types=1);

namespace CommonTest\Util;

use Common\Util\MemoryLimit;
use PHPUnit\Framework\TestCase;

class MemoryLimitTest extends TestCase
{
	private string $originalLimit;

	protected function setUp(): void
	{
		parent::setUp();

		$this->originalLimit = (string)ini_get('memory_limit');
	}

	protected function tearDown(): void
	{
		ini_set('memory_limit', $this->originalLimit);

		parent::tearDown();
	}

	public function test_raises_limit()
	{
		ini_set('memory_limit', '128M');

		MemoryLimit::gigabyte(2);

		self::assertSame('2G', ini_get('memory_limit'));
	}

	public function test_does_not_lower_limit()
	{
		MemoryLimit::gigabyte(4);
		MemoryLimit::gigabyte(2);

		self::assertSame('4G', ini_get('memory_limit'));
	}

	public function test_does_not_lower_limit_across_units()
	{
		MemoryLimit::gigabyte(1);
		MemoryLimit::megabyte(512);

		self::assertSame('1G', ini_get('memory_limit'));
	}

	public function test_raises_limit_across_units()
	{
		ini_set('memory_limit', '128M');

		MemoryLimit::megabyte(512);
		MemoryLimit::gigabyte(1);

		self::assertSame('1G', ini_get('memory_limit'));
	}

	public function test_equal_limit_is_a_noop()
	{
		MemoryLimit::gigabyte(2);
		MemoryLimit::megabyte(2048);

		self::assertSame('2G', ini_get('memory_limit'));
	}

	public function test_lowers_limit_when_raise_only_is_disabled()
	{
		MemoryLimit::gigabyte(4);
		MemoryLimit::gigabyte(2, raiseOnly: false);

		self::assertSame('2G', ini_get('memory_limit'));
	}

	public function test_lowers_unlimited_when_raise_only_is_disabled()
	{
		ini_set('memory_limit', '-1');

		MemoryLimit::megabyte(512, raiseOnly: false);

		self::assertSame('512M', ini_get('memory_limit'));
	}

	public function test_unlimited_is_never_replaced()
	{
		ini_set('memory_limit', '-1');

		MemoryLimit::gigabyte(4);

		self::assertSame('-1', ini_get('memory_limit'));
	}
}
