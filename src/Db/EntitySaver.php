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
				$this->entityManager->flush($entity);
				$this->entityManager->commit();

				break;
			}
			catch (RetryableException $e)
			{
				$this->entityManager->rollback();

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
				$this->entityManager->rollback();
				throw $e;
			}
		}
	}
}