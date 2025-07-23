<?php
namespace Common;

use Common\Db\Functions\Distance;
use Common\Router\HttpRouteCreator;
use Common\View\Helper\AbsoluteUrl;
use Common\View\Helper\AbsoluteUrlFactory;
use Common\View\Helper\Config;
use Common\View\Helper\ConfigFactory;
use Common\View\Helper\StaticResource;
use Common\View\Helper\StaticResourceFactory;
use Mezzio\Application;
use Mezzio\Container\ApplicationConfigInjectionDelegator;

return [

	'common' => [
		'translator' => [
			'global' => [
				'enabled' => true,
			],
		],
	],

	'routes' => [
		'common' => HttpRouteCreator::create()
			->setRoute('/common')
			->setChildRoutes(
				[
					'country' => require 'routes/country.php',
				]
			),
	],

	'doctrine' => [
		'configuration' => [
			'orm_default' => [
				'string_functions' => [
					Distance::NAME => Distance::class,
				],
			],
		],
	],

	'view_helpers' => [
		'factories' => [
			StaticResource::class => StaticResourceFactory::class,
			AbsoluteUrl::class    => AbsoluteUrlFactory::class,
			Config::class         => ConfigFactory::class,
		],
		'aliases'   => [
			'staticResource' => StaticResource::class,
			'absoluteUrl'    => AbsoluteUrl::class,
			'config'         => Config::class,
		],
	],

	'dependencies' => [
		'abstract_factories' => [
			DefaultFactory::class,
		],
		'delegators'         => [
			Application::class => [
				ApplicationConfigInjectionDelegator::class,
			],
		],
	],
];