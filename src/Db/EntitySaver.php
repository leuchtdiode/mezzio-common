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
	 * @throws Throwable
	 */
	public function flush(?Entity $entity = null): void
	{
		$attempt = 0;

		while (true)
		{
			try
			{
				$this->entityManager->beginTransaction();
				$this->entityManager->flush();
				$this->entityManager->commit();

				break;
			}
			catch (RetryableException $e)
			{
				error_log(sprintf(
					'Entity saver retryable exception: %s - %s',
					$entity
						? get_class($entity)
						: 'class n.a.',
					$e->getMessage(),
				));

				$this->rollbackQuietly();

				$attempt++;

				if ($attempt > self::RETRIES)
				{
					throw $e;
				}

				$sleep = pow(2, $attempt) * self::SLEEP_MS; // exponential backoff
				usleep($sleep);
			}
			catch (Throwable $e)
			{
				error_log(sprintf(
					'Entity saver exception: %s - %s',
					$entity
						? get_class($entity)
						: 'class n.a.',
					$e->getMessage(),
				));

				$this->rollbackQuietly();
				throw $e;
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
		}
	}
}