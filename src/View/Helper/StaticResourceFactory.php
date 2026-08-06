<?php
namespace Common\View\Helper;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class StaticResourceFactory implements FactoryInterface
{
	/**
	 * @param ContainerInterface $container
	 * @param string $requestedName
	 * @param array|null $options
	 * @return StaticResource
	 */
	public function __invoke(
		ContainerInterface $container,
		$requestedName,
		?array $options = null
	)
	{
		return new StaticResource(
			getcwd() . '/public'
		);
	}
}
