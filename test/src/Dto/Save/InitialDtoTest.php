<?php
declare(strict_types=1);

namespace CommonTest\Dto\Save;

use Common\Dto\BaseSaveData;
use Common\Dto\BaseSaveParams;
use Common\Dto\BaseSaver;
use Common\Dto\DefaultMapper;
use Common\Dto\KeyConfig;
use Common\Dto\Save\Transformer;
use Common\Dto\Save\Validator;
use CommonTest\Base;
use CommonTest\Dto\Save\Fixture\Db\Attachment\Entity as AttachmentEntity;
use CommonTest\Dto\Save\Fixture\Db\Thing\Entity as ThingEntity;
use CommonTest\Dto\Save\Fixture\Thing\RecordingPostSave;
use CommonTest\Dto\Save\Fixture\Thing\Saver;
use CommonTest\Dto\Save\Fixture\Thing\SaverWithoutInitialDto;
use CommonTest\Dto\Save\Fixture\Thing\SaverWithoutSaveConfig;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * #[SaveConfig(provideInitialDto: true)] makes BaseSaver hand the PostSave handler the dto as it looked
 * before the save. Without the flag nothing is mapped, so savers that do not need it keep their old cost.
 */
class InitialDtoTest extends Base
{
	private const string THING_NAMESPACE       = 'CommonTest\Dto\Save\Fixture\Thing';
	private const string THING_DB_NAMESPACE    = 'CommonTest\Dto\Save\Fixture\Db\Thing';
	private const string ATTACHMENT_NAMESPACE  = 'CommonTest\Dto\Save\Fixture\Attachment';
	private const string ATTACHMENT_DB_NAMESPACE = 'CommonTest\Dto\Save\Fixture\Db\Attachment';

	private EntityManager     $entityManager;
	private RecordingPostSave $postSave;
	private ContainerInterface $container;

	protected function isDatabaseNecessary(): bool
	{
		return false;
	}

	protected function setUp(): void
	{
		parent::setUp();

		$configuration = ORMSetup::createAttributeMetadataConfiguration(
			[ __DIR__ . '/Fixture/Db' ],
			true
		);

		$configuration->setQueryCache(new ArrayAdapter(0, false));
		$configuration->setMetadataCache(new ArrayAdapter(0, false));
		$configuration->enableNativeLazyObjects(true);

		$this->entityManager = new EntityManager(
			DriverManager::getConnection(
				[
					'driver' => 'pdo_sqlite',
					'memory' => true,
				],
				$configuration
			),
			$configuration
		);

		(new SchemaTool($this->entityManager))->createSchema(
			$this->entityManager
				->getMetadataFactory()
				->getAllMetadata()
		);

		$this->postSave = new RecordingPostSave();

		$entityManager = $this->entityManager;
		$postSave      = $this->postSave;

		$this->container = new class ($entityManager, $postSave) implements ContainerInterface {
			public function __construct(
				private readonly EntityManager $entityManager,
				private readonly RecordingPostSave $postSave
			)
			{
			}

			public function get(string $id): mixed
			{
				if ($id === RecordingPostSave::class)
				{
					return $this->postSave;
				}

				if (str_ends_with($id, '\Repository'))
				{
					return $this->entityManager->getRepository(
						substr($id, 0, -strlen('\Repository')) . '\Entity'
					);
				}

				throw new \RuntimeException('Unexpected container id ' . $id);
			}

			public function has(string $id): bool
			{
				return true;
			}
		};
	}

	public function test_initial_dto_is_null_for_an_addition(): void
	{
		$result = $this->createSaver(Saver::class)->save(
			BaseSaveParams::create()
				->setData(
					BaseSaveData::create()
						->setName('first')
				)
		);

		$this->assertTrue($result->isSuccess());
		$this->assertTrue($this->postSave->getParams()->isAddition());
		$this->assertNull($this->postSave->getParams()->getInitialDto());
	}

	public function test_initial_dto_holds_the_values_from_before_the_update(): void
	{
		$thing = $this->persistThing('before');

		$this->createSaver(Saver::class)->save(
			BaseSaveParams::create()
				->setDtoId($thing->getId())
				->setData(
					BaseSaveData::create()
						->setName('after')
				)
		);

		$params = $this->postSave->getParams();

		$this->assertFalse($params->isAddition());
		$this->assertSame('before', $params->getInitialDto()->getName());
		$this->assertSame('after', $params->getDto()->getName());
	}

	/**
	 * The case this was built for: a replaced to-one association. BaseSaver only repoints the owning
	 * side's reference, so the initial dto still wraps the attachment that was there before.
	 */
	public function test_initial_dto_keeps_the_replaced_association(): void
	{
		$old = new AttachmentEntity();
		$old->setFileName('old.pdf');

		$new = new AttachmentEntity();
		$new->setFileName('new.pdf');

		$this->entityManager->persist($old);
		$this->entityManager->persist($new);

		$thing = $this->persistThing('with attachment', $old);

		$this->createSaver(Saver::class)->save(
			BaseSaveParams::create()
				->setDtoId($thing->getId())
				->setData(
					BaseSaveData::create()
						->setAttachment($new->getId())
				)
		);

		$params = $this->postSave->getParams();

		$this->assertSame($old->getId(), $params->getInitialDto()->getAttachment()->getId());
		$this->assertSame($new->getId(), $params->getDto()->getAttachment()->getId());
	}

	public function test_initial_dto_keeps_the_association_that_was_cleared(): void
	{
		$attachment = new AttachmentEntity();
		$attachment->setFileName('removed.pdf');

		$this->entityManager->persist($attachment);

		$thing = $this->persistThing('with attachment', $attachment);

		$this->createSaver(Saver::class)->save(
			BaseSaveParams::create()
				->setDtoId($thing->getId())
				->setData(
					BaseSaveData::create()
						->setAttachment(null)
				)
		);

		$params = $this->postSave->getParams();

		$this->assertSame($attachment->getId(), $params->getInitialDto()->getAttachment()->getId());
		$this->assertNull($params->getDto()->getAttachment());
	}

	public function test_initial_dto_is_null_without_the_flag(): void
	{
		$thing = $this->persistThing('before');

		$this->createSaver(SaverWithoutInitialDto::class)->save(
			BaseSaveParams::create()
				->setDtoId($thing->getId())
				->setData(
					BaseSaveData::create()
						->setName('after')
				)
		);

		$params = $this->postSave->getParams();

		$this->assertFalse($params->isAddition());
		$this->assertNull($params->getInitialDto());
		$this->assertSame('after', $params->getDto()->getName());
	}

	public function test_saver_without_save_config_still_saves(): void
	{
		$thing = $this->persistThing('before');

		$result = $this->createSaver(SaverWithoutSaveConfig::class)->save(
			BaseSaveParams::create()
				->setDtoId($thing->getId())
				->setData(
					BaseSaveData::create()
						->setName('after')
				)
		);

		$this->assertTrue($result->isSuccess());
		$this->assertSame('after', $result->getDto()->getName());
		$this->assertNull($this->postSave->getParams());
	}

	private function persistThing(string $name, ?AttachmentEntity $attachment = null): ThingEntity
	{
		$thing = new ThingEntity();
		$thing->setName($name);
		$thing->setAttachment($attachment);

		$this->entityManager->persist($thing);
		$this->entityManager->flush();

		return $thing;
	}

	/**
	 * @param class-string<BaseSaver> $saverClass
	 */
	private function createSaver(string $saverClass): BaseSaver
	{
		$keyConfig = new KeyConfig([
			'common' => [
				'dto' => [
					'test--thing'      => [
						'namespace'   => self::THING_NAMESPACE,
						'dbNamespace' => self::THING_DB_NAMESPACE,
					],
					'test--attachment' => [
						'namespace'   => self::ATTACHMENT_NAMESPACE,
						'dbNamespace' => self::ATTACHMENT_DB_NAMESPACE,
					],
				],
			],
		]);

		return new $saverClass(
			$this->container,
			$this->entityManager,
			$keyConfig,
			new DefaultMapper($this->container, $keyConfig),
			new Validator($this->entityManager),
			new Transformer()
		);
	}
}
