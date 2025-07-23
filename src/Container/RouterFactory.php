<?php
declare(strict_types=1);

namespace Common\Container;

use Laminas\Router\Http\TreeRouteStack;
use Mezzio\Router\LaminasRouter;
use Psr\Container\ContainerInterface;

class RouterFactory
{
	/**
	 * @param ContainerInterface $container
	 * @return LaminasRouter
	 */
	public function __invoke(ContainerInterface $container)
	{
		return new LaminasRouter($container->get(TreeRouteStack::class));
	}
}