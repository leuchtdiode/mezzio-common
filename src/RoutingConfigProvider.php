<?php

declare(strict_types=1);

namespace Common;

use Common\Container\RouterFactory;
use Mezzio\Router\RouterInterface;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class RoutingConfigProvider
{
	/**
	 * Returns the configuration array
	 *
	 * To add a bit of a structure, each section is defined in a separate
	 * method which returns an array with its configuration.
	 */
	public function __invoke(): array
	{
		return [
			'dependencies' => $this->getDependencies(),
		];
	}

	/**
	 * Returns the container dependencies
	 */
	public function getDependencies(): array
	{
		return [
			'factories' => [
				RouterInterface::class => RouterFactory::class,
			],
		];
	}
}
