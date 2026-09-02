<?php

namespace CommonTest\Db;

use Common\Db\EntitySaver;
use CommonTest\Base;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\NoActiveTransaction;
use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use RuntimeException;

/**
 * Doctrine already closes the entity manager and rolls the transaction back while it handles a
 * failed flush, and dbal drops the nesting level to zero when a commit fails. The rollback the
 * saver does afterwards therefore regularly fails on its own with NoActiveTransaction. It must
 * never replace the exception that actually caused the failure.
 */
class EntitySaverTest extends Base
{
	private Connection&Stub $connection;

	private EntityManager&MockObject $entityManager;

	protected function setUp(): void
	{
		parent::setUp();

		$this->connection = $this->createStub(Connection::class);

		$this->entityManager = $this
			->getMockBuilder(EntityManager::class)
			->disableOriginalConstructor()
			->onlyMethods([ 'beginTransaction', 'flush', 'commit', 'rollback', 'getConnection' ])
			->getMock();

		$this->entityManager
			->method('getConnection')
			->willReturn($this->connection);
	}

	public function test_a_failing_rollback_does_not_replace_the_flush_exception(): void
	{
		$this->connection
			->method('isTransactionActive')
			->willReturn(true);

		$this->entityManager
			->method('flush')
			->willThrowException(new RuntimeException('the real cause'));

		$this->entityManager
			->expects($this->once())
			->method('rollback')
			->willThrowException(NoActiveTransaction::new());

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('the real cause');

		$this->getEntitySaver()->flush();
	}

	/**
	 * Dbal raises the nesting level before it asks the driver, so a driver level failure inside
	 * beginTransaction() leaves the connection looking like it still has a transaction.
	 */
	public function test_a_failing_rollback_does_not_replace_the_begin_transaction_exception(): void
	{
		$this->connection
			->method('isTransactionActive')
			->willReturn(true);

		$this->entityManager
			->method('beginTransaction')
			->willThrowException(new RuntimeException('could not begin'));

		$this->entityManager
			->expects($this->once())
			->method('rollback')
			->willThrowException(NoActiveTransaction::new());

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('could not begin');

		$this->getEntitySaver()->flush();
	}

	public function test_no_rollback_is_attempted_without_an_active_transaction(): void
	{
		$this->connection
			->method('isTransactionActive')
			->willReturn(false);

		$this->entityManager
			->method('commit')
			->willThrowException(new RuntimeException('commit failed'));

		$this->entityManager
			->expects($this->never())
			->method('rollback');

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('commit failed');

		$this->getEntitySaver()->flush();
	}

	public function test_a_failing_rollback_does_not_break_the_retry_loop(): void
	{
		$this->connection
			->method('isTransactionActive')
			->willReturn(true);

		$deadlock = new class('deadlock detected') extends RuntimeException implements RetryableException
		{
		};

		$attempts = 0;

		$this->entityManager
			->method('flush')
			->willReturnCallback(static function () use (&$attempts, $deadlock): void
			{
				$attempts++;

				if ($attempts === 1)
				{
					throw $deadlock;
				}
			});

		$this->entityManager
			->expects($this->once())
			->method('rollback')
			->willThrowException(NoActiveTransaction::new());

		$this->getEntitySaver()->flush();

		$this->assertSame(2, $attempts, 'the deadlock was not retried');
	}

	private function getEntitySaver(): EntitySaver
	{
		return new EntitySaver($this->entityManager);
	}
}
