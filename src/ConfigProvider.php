<?php

declare(strict_types=1);

namespace Common;

use Common\Router\FlattenRoutes;

/**
 * The configuration provider for the module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
	/**
	 * Returns the configuration array
	 *
	 * To add a bit of a structure, each section is defined in a separate
	 * method which returns an array with its configuration.
	 */
	public function __invoke(): array
	{
		$config = require __DIR__ . '/../config/module.config.php';

		$config['routes'] = FlattenRoutes::flatten($config['routes']);

		return $config;
	}
}
