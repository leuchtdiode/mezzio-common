<?php
declare(strict_types=1);

namespace Common\Console;

use Mezzio\MiddlewareFactoryInterface;
use Mezzio\Router\Route;
use Mezzio\Router\RouterInterface;

readonly class RoutesInitializer
{
	public function __construct(
		private array $config,
		private MiddlewareFactoryInterface $middlewareFactory,
		private RouterInterface $router
	)
	{
	}

	public function __invoke(): void
	{
		$routes = $this->config['routes'] ?? [];

		foreach ($routes as $name => $route)
		{
			$this->router->addRoute(
				new Route(
					path: $route['path'],
					middleware: $this->middlewareFactory->prepare($route['middleware']),
					methods: $route['allowed_methods'] ?? null,
					name: $name
				)
			);
		}
	}
}