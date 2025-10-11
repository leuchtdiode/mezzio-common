<?php
declare(strict_types=1);

namespace Common\Action;

use Common\Health\Checker;
use Common\Health\CheckResult;
use Common\Hydration\ObjectToArrayHydrator;
use Common\Util\IpUtil;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Health extends BaseJsonAction
{
	public function __construct(
		private readonly array $config,
		private readonly Checker $checker
	)
	{
	}

	/**
	 * @throws Throwable
	 */
	public function executeAction(): ResponseInterface
	{
		$config = $this->config['common']['health'];

		if (!$config['enabled'])
		{
			return new EmptyResponse(404);
		}

		if (
			($restrictedIps = $config['restrictedIps'] ?? [])
			&& !$this->ipAllowed(IpUtil::getIp(), $restrictedIps)
		)
		{
			return new EmptyResponse(403);
		}

		$checkResults = $this->checker->check();

		$unhealthy = array_any(
			$checkResults,
			fn(CheckResult $result) => !$result->isHealthy()
		);

		$response = JsonResponse::is();

		if ($unhealthy)
		{
			$response->unsuccessful();
		}
		else
		{
			$response->successful();
		}

		$response->data(
			ObjectToArrayHydrator::hydrate($checkResults)
		);

		return $response->dispatch();
	}

	protected function ipAllowed(string $ip, array $ips): bool
	{
		$ok = false;

		foreach ($ips as $ipToCheck)
		{
			$ipToCheck = str_replace('.', '\.', $ipToCheck);
			$ipToCheck = str_replace('*', '\d+', $ipToCheck);

			$regex = '#^' . $ipToCheck . '$#';

			if (preg_match($regex, $ip))
			{
				$ok = true;
				break;
			}
		}

		return $ok;
	}
}