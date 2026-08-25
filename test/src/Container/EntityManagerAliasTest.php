<?php
declare(strict_types=1);

namespace CommonTest\Container;

use Common\ConfigProvider;
use CommonTest\Base;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Laminas\ServiceManager\ServiceManager;

/**
 * EntityManager::class, EntityManagerInterface::class and 'doctrine.entity_manager.orm_default' must all
 * resolve to one shared instance. Registering EntityManagerFactory under each of the three names built
 * three EntityManagers with three connections and three identity maps, so an entity persisted through the
 * name the application uses was invisible to anything resolving one of the other two - most notably the
 * doctrine migrations tooling, which looks up 'doctrine.entity_manager.orm_default' by convention in
 * Roave\PsrContainerDoctrine\AbstractFactory::retrieveDependency().
 */
class EntityManagerAliasTest extends Base
{
	private const string ENTITY_MANAGER_SERVICE = 'doctrine.entity_manager.orm_default';

	private ServiceManager $container;

	protected function isDatabaseNecessary(): bool
	{
		return false;
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->container = $this->createContainer();
	}

	public function test_class_and_interface_resolve_to_the_same_instance(): void
	{
		self::assertSame(
			$this->container->get(EntityManager::class),
			$this->container->get(EntityManagerInterface::class)
		);
	}

	public function test_doctrine_service_name_resolves_to_the_same_instance(): void
	{
		self::assertSame(
			$this->container->get(self::ENTITY_MANAGER_SERVICE),
			$this->container->get(EntityManager::class)
		);
	}

	/**
	 * A separate EntityManager also means a separate connection, which is what makes the duplication
	 * expensive rather than merely redundant.
	 */
	public function test_every_name_shares_a_single_connection(): void
	{
		$connections = [
			$this->container->get(self::ENTITY_MANAGER_SERVICE)->getConnection(),
			$this->container->get(EntityManager::class)->getConnection(),
			$this->container->get(EntityManagerInterface::class)->getConnection(),
		];

		self::assertCount(1, array_unique(array_map(spl_object_id(...), $connections)));
	}

	/**
	 * Guards the wiring itself, so that reintroducing a factory for one of the class names fails here even
	 * if the container happens to hand out a shared instance for another reason.
	 */
	public function test_class_names_are_wired_as_aliases_and_not_as_factories(): void
	{
		$dependencies = (new ConfigProvider())()['dependencies'];

		self::assertSame(self::ENTITY_MANAGER_SERVICE, $dependencies['aliases'][EntityManager::class] ?? null);
		self::assertSame(self::ENTITY_MANAGER_SERVICE, $dependencies['aliases'][EntityManagerInterface::class] ?? null);

		self::assertArrayNotHasKey(EntityManager::class, $dependencies['factories']);
		self::assertArrayNotHasKey(EntityManagerInterface::class, $dependencies['factories']);
		self::assertArrayHasKey(self::ENTITY_MANAGER_SERVICE, $dependencies['factories']);
	}

	/**
	 * Mirrors config/container.php. The library ships no connection params and no mapping driver, both are
	 * supplied by the consuming application, so minimal in memory equivalents stand in for them here.
	 */
	private function createContainer(): ServiceManager
	{
		$config = (new ConfigProvider())();

		$config['doctrine']['connection']['orm_default'] = [
			'params' => [
				'driver' => 'pdo_sqlite',
				'memory' => true,
			],
		];

		$config['doctrine']['driver']['orm_default'] = [
			'class' => AttributeDriver::class,
			'paths' => [ __DIR__ ],
		];

		$dependencies                       = $config['dependencies'];
		$dependencies['services']['config'] = $config;

		return new ServiceManager($dependencies);
	}
}
