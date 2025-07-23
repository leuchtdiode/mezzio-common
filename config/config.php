<?php

declare(strict_types=1);

use Common\ConfigProvider;
use Common\RoutingConfigProvider;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
$cacheConfig = [
	'config_cache_path' => 'data/cache/config-cache.php',
];

$aggregator = new ConfigAggregator([
	new ArrayProvider($cacheConfig),
	RoutingConfigProvider::class,
	\Laminas\I18n\ConfigProvider::class,
	\Laminas\Router\ConfigProvider::class,
	ConfigProvider::class,
	\Mezzio\ConfigProvider::class,
	\Mezzio\Router\ConfigProvider::class,
	\Laminas\Cache\ConfigProvider::class,
	\Laminas\Cache\Storage\Adapter\Memory\ConfigProvider::class,
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
