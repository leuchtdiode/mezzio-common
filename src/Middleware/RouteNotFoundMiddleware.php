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

readonly class RouteNotFoundMiddleware implements MiddlewareInterface
{
	public function __construct(
		private array $config,
		private ResponseFactoryInterface $responseFactory,
		private TemplateRendererInterface $renderer,
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

		$config = $this->config['common']['routing']['errorHandler'];

		$response = $this->responseFactory
			->createResponse()
			->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);

		$response
			->getBody()
			->write(
				$this->renderer->render($config['template'], [ 'request' => $request, 'layout' => $config['layout'] ])
			);

		return $response;
	}
}