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
 * failed flush, and dbal keeps the nesting level up when rolling back to a savepoint fails. The
 * rollback the saver does afterwards therefore regularly fails on its own. It must never replace
 * the exception that actually caused the failure.
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
			->onlyMethods([
				'beginTransaction',
				'commit',
				'flush',
				'rollback',
				'getConnection',
				'isOpen',
			])
			->getMock();

		$this->entityManager
			->method('getConnection')
			->willReturn($this->connection);
	}

	/**
	 * UnitOfWork::commit() opens its own transaction. A second one around it makes doctrine work
	 * with a savepoint, and a deadlock drops that savepoint, which turns the deadlock into an
	 * unrelated "SAVEPOINT DOCTRINE_2 does not exist" error.
	 */
	public function test_the_saver_does_not_open_its_own_transaction(): void
	{
		$this->entityManager
			->expects($this->never())
			->method('beginTransaction');

		$this->entityManager
			->expects($this->never())
			->method('commit');

		$this->entityManager
			->expects($this->once())
			->method('flush');

		$this->getEntitySaver()->flush();
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

	public function test_no_rollback_is_attempted_without_an_active_transaction(): void
	{
		$this->connection
			->method('isTransactionActive')
			->willReturn(false);

		$this->entityManager
			->method('flush')
			->willThrowException(new RuntimeException('the real cause'));

		$this->entityManager
			->expects($this->never())
			->method('rollback');

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('the real cause');

		$this->getEntitySaver()->flush();
	}

	/**
	 * Dbal lowers the nesting level only after a successful rollback, so a connection whose
	 * rollback failed would keep pretending to be inside a transaction the server already dropped.
	 */
	public function test_a_failing_rollback_closes_the_connection(): void
	{
		$closed = false;

		$this->connection
			->method('isTransactionActive')
			->willReturn(true);

		$this->connection
			->method('close')
			->willReturnCallback(static function () use (&$closed): void
			{
				$closed = true;
			});

		$this->entityManager
			->method('flush')
			->willThrowException(new RuntimeException('the real cause'));

		$this->entityManager
			->expects($this->once())
			->method('rollback')
			->willThrowException(NoActiveTransaction::new());

		try
		{
			$this->getEntitySaver()->flush();
		}
		catch (RuntimeException)
		{
			// expected, this test is about what happened to the connection
		}

		$this->assertTrue($closed, 'the connection was left with a stale transaction nesting level');
	}

	public function test_a_retryable_exception_is_retried_while_the_entity_manager_is_open(): void
	{
		$this->connection
			->method('isTransactionActive')
			->willReturn(true);

		$this->entityManager
			->method('isOpen')
			->willReturn(true);

		$attempts = 0;
		$deadlock = $this->getDeadlock();

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

	/**
	 * Doctrine closes the entity manager while handling the failed flush, so a further attempt
	 * could only raise EntityManagerClosed, which would hide the deadlock from the caller.
	 */
	public function test_a_closed_entity_manager_ends_the_retry_loop_with_the_original_exception(): void
	{
		$this->connection
			->method('isTransactionActive')
			->willReturn(true);

		$this->entityManager
			->method('isOpen')
			->willReturn(false);

		$deadlock = $this->getDeadlock();

		$this->entityManager
			->expects($this->once())
			->method('flush')
			->willThrowException($deadlock);

		$this->expectExceptionObject($deadlock);

		$this->getEntitySaver()->flush();
	}

	private function getDeadlock(): RetryableException
	{
		return new class('deadlock detected') extends RuntimeException implements RetryableException
		{
		};
	}

	private function getEntitySaver(): EntitySaver
	{
		return new EntitySaver($this->entityManager);
	}
}
