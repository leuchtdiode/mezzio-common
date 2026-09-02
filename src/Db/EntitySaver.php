<?php
namespace Common\Db;

use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\ORM\EntityManager;
use Throwable;

class EntitySaver
{
	private const int RETRIES  = 5;
	private const int SLEEP_MS = 250000; // 250ms -> exponential waiting

	public function __construct(
		protected EntityManager $entityManager
	)
	{
	}

	/**
	 * @throws Throwable
	 */
	public function save(Entity $entity, bool $flush = true): void
	{
		$this->entityManager->persist($entity);

		if ($flush)
		{
			$this->flush($entity);
		}
	}

	/**
	 * No transaction is opened here on purpose. UnitOfWork::commit() already wraps the flush in one,
	 * so an additional outer transaction would only turn doctrine's into a nested one. A deadlock
	 * drops the savepoint that doctrine then wants to roll back to, which makes its own rollback
	 * fail with "SAVEPOINT DOCTRINE_2 does not exist" - and that error replaces the deadlock we are
	 * trying to detect down here.
	 *
	 * @throws Throwable
	 */
	public function flush(?Entity $entity = null): void
	{
		$attempt = 0;

		while (true)
		{
			try
			{
				$this->entityManager->flush();

				break;
			}
			catch (Throwable $e)
			{
				$retryable = $e instanceof RetryableException;

				error_log(sprintf(
					'Entity saver %s: %s - %s',
					$retryable
						? 'retryable exception'
						: 'exception',
					$entity
						? get_class($entity)
						: 'class n.a.',
					$e->getMessage(),
				));

				$this->rollbackQuietly();

				if (!$retryable)
				{
					throw $e;
				}

				$attempt++;

				// doctrine closes the entity manager while it handles a failed flush, so every
				// further attempt could only raise EntityManagerClosed and would hide the deadlock
				// from the caller. Retrying needs a rebuilt entity manager, which we do not have.
				if ($attempt > self::RETRIES || !$this->entityManager->isOpen())
				{
					throw $e;
				}

				$sleep = pow(2, $attempt) * self::SLEEP_MS; // exponential backoff
				usleep($sleep);
			}
		}
	}

	/**
	 * Rolling back can fail on its own, e.g. because doctrine already rolled the transaction back
	 * while handling the failed flush, or because the connection is gone. Such a failure must never
	 * replace the exception we are about to rethrow.
	 */
	private function rollbackQuietly(): void
	{
		try
		{
			if (!$this->entityManager->getConnection()->isTransactionActive())
			{
				return;
			}

			$this->entityManager->rollback();
		}
		catch (Throwable $rollbackException)
		{
			error_log(sprintf(
				'Entity saver rollback failed: %s - %s',
				get_class($rollbackException),
				$rollbackException->getMessage(),
			));

			// dbal lowers the transaction nesting level only after a successful rollback, so the
			// connection would keep pretending to be inside a transaction the server already
			// dropped. Closing it resets the level and reconnects on the next statement.
			$this->entityManager->getConnection()->close();
		}
	}
}