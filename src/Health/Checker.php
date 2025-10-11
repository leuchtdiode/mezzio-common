<?php
declare(strict_types=1);

namespace Common\Health;

use LogicException;
use Psr\Container\ContainerInterface;
use Throwable;

readonly class Checker
{
	public function __construct(
		private array $config,
		private ContainerInterface $container
	)
	{
	}

	/**
	 * @return CheckResult[]
	 * @throws Throwable
	 */
	public function check(): array
	{
		$config = $this->config['common']['health'] ?? [];

		$checkResults = [];

		foreach ($config['checkers'] as $checkerClass)
		{
			$checker = $this->container->get($checkerClass);

			if (!$checker instanceof Check)
			{
				throw new LogicException('Checker must implement ' . Check::class);
			}

			$checkResults[] = $checker->check();
		}

		return $checkResults;
	}
}