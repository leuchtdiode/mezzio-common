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
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command as MigrationsCommand;
use Mezzio\Application;
use Mezzio\Container\ApplicationConfigInjectionDelegator;
use Roave\PsrContainerDoctrine\EntityManagerFactory;
use Roave\PsrContainerDoctrine\Migrations\CommandFactory;
use Roave\PsrContainerDoctrine\Migrations\ConfigurationLoaderFactory;
use Roave\PsrContainerDoctrine\Migrations\DependencyFactoryFactory;

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
		'factories'          => [
			// doctrine cli
			'doctrine.entity_manager.orm_default'        => EntityManagerFactory::class,
			MigrationsCommand\CurrentCommand::class      => CommandFactory::class,
			MigrationsCommand\DiffCommand::class         => CommandFactory::class,
			MigrationsCommand\DumpSchemaCommand::class   => CommandFactory::class,
			MigrationsCommand\ExecuteCommand::class      => CommandFactory::class,
			MigrationsCommand\GenerateCommand::class     => CommandFactory::class,
			MigrationsCommand\LatestCommand::class       => CommandFactory::class,
			MigrationsCommand\ListCommand::class         => CommandFactory::class,
			MigrationsCommand\MigrateCommand::class      => CommandFactory::class,
			MigrationsCommand\RollupCommand::class       => CommandFactory::class,
			MigrationsCommand\StatusCommand::class       => CommandFactory::class,
			MigrationsCommand\SyncMetadataCommand::class => CommandFactory::class,
			MigrationsCommand\UpToDateCommand::class     => CommandFactory::class,
			MigrationsCommand\VersionCommand::class      => CommandFactory::class,
			ConfigurationLoader::class                   => ConfigurationLoaderFactory::class,
			DependencyFactory::class                     => DependencyFactoryFactory::class,
		],
	],
];