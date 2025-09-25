<?php
declare(strict_types=1);

namespace Common\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class HttpHeaderSecurityMiddleware implements MiddlewareInterface
{
	public function __construct(
		private array $config
	)
	{
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$protocol = $this->config['common']['url']['protocol'] ?? null;

		$request = $request
			->withHeader('X-Frame-Options', 'DENY')
			->withHeader('X-Content-Type-Options', 'nosniff');

		if ($protocol === 'https')
		{
			$request = $request
				->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		}

		return $handler->handle($request);
	}
}