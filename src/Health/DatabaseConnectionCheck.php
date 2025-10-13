<?php
declare(strict_types=1);

namespace Common\Health;

use Doctrine\ORM\EntityManager;
use Exception;
use Monitoring\Health\Check;
use Monitoring\Health\CheckResult;
use Throwable;

readonly class DatabaseConnectionCheck implements Check
{
	public function __construct(
		private EntityManager $entityManager
	)
	{
	}

	/**
	 * @throws Throwable
	 */
	public function check(): CheckResult
	{
		if (!interface_exists('\Monitoring\Health\Check'))
		{
			throw new Exception('leuchtdiode/mezzio-monitoring is mandatory');
		}

		$result = new CheckResult();
		$result->setKey('database-connection');

		echo 'test';
		exit;

		return $result;
	}
}