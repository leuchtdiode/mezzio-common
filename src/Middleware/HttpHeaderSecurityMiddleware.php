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

		$response = $handler->handle($request);

		$response = $response
			->withAddedHeader('x-frame-options', 'DENY')
			->withAddedHeader('x-content-type-Options', 'nosniff')
			->withAddedHeader('x-powered-by', '');

		if ($protocol === 'https')
		{
			$response = $response
				->withAddedHeader('strict-transport-security', 'max-age=31536000; includeSubDomains; preload');
		}

		return $response;
	}
}