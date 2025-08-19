<?php
declare(strict_types=1);

namespace Common\Middleware;

use Laminas\Diactoros\Response\EmptyResponse;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteNotFoundEmptyResponseMiddleware implements MiddlewareInterface
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		/**
		 * @var RouteResult $routeResult
		 */
		$routeResult = $request->getAttribute(RouteResult::class, false);

		if (empty($routeResult->getMatchedRouteName()))
		{
			return new EmptyResponse(404);
		}

		return $handler->handle($request);
	}
}