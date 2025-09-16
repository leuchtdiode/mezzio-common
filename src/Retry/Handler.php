<?php
declare(strict_types=1);

namespace Common\Retry;

use Throwable;

class Handler
{
	/**
	 * @throws Throwable
	 */
	public function handle(HandleParams $params): void
	{
		$this->execute($params, 1);
	}

	/**
	 * @throws Throwable
	 */
	private function execute(HandleParams $params, int $try): void
	{
		$callable = $params->getCallable();

		try
		{
			$callable();
		}
		catch (Throwable $t)
		{
			if (++$try <= $params->getTries())
			{
				sleep($params->getTimeout());

				$this->execute($params, $try);

				return;
			}

			throw $t;
		}
	}
}
