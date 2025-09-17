<?php
declare(strict_types=1);

namespace Common\Retry;

use RuntimeException;
use Throwable;

class Handler
{
	/**
	 * @throws Throwable
	 */
	public function handle(HandleParams $params): mixed
	{
		$try = 1;

		$callable = $params->getCallable();

		$exception = null;

		while ($try++ <= $params->getTries())
		{
			try
			{
				return $callable();
			}
			catch (Throwable $t)
			{
				$exception = $t;

				sleep($params->getTimeout());
			}
		}

		throw $exception ?? new RuntimeException('Unknown retry error');
	}
}