<?php
declare(strict_types=1);

namespace Common\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Mezzio\Router\RouteResult;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteNotFoundMiddleware implements MiddlewareInterface
{
	public function __construct(
		private readonly ResponseFactoryInterface $responseFactory,
		private readonly TemplateRendererInterface $renderer,
	)
	{
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		/**
		 * @var RouteResult $routeResult
		 */
		$routeResult = $request->getAttribute(RouteResult::class);

		if (!empty($routeResult->getMatchedRoute()))
		{
			return $handler->handle($request);
		}

		$response = $this->responseFactory
			->createResponse()
			->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);

		$response
			->getBody()
			->write(
				$this->renderer->render('error::error', [ 'request' => $request, 'layout' => 'layout::default' ])
			);

		return $response;
	}
}