<?php
declare(strict_types=1);

namespace CommonTest\Dto\Save\Fixture\Db\Thing;

use Common\Db\Entity as DbEntity;
use Common\Dto\EntityConfig;
use Common\Dto\PropertyConfig;
use CommonTest\Dto\Save\Fixture\Db\Attachment\Entity as AttachmentEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'fixture_thing')]
#[ORM\Entity(repositoryClass: Repository::class)]
#[EntityConfig(dtoKey: 'test--thing')]
class Entity implements DbEntity
{
	#[ORM\Id]
	#[ORM\Column(type: 'string')]
	#[PropertyConfig(validationLabel: 'ID')]
	private string $id;

	#[ORM\Column(type: 'string')]
	#[PropertyConfig(validationLabel: 'Name')]
	private string $name = '';

	#[ORM\ManyToOne(targetEntity: AttachmentEntity::class)]
	#[ORM\JoinColumn(name: 'attachment', nullable: true)]
	#[PropertyConfig(validationLabel: 'Attachment')]
	private ?AttachmentEntity $attachment = null;

	public function __construct(?string $id = null)
	{
		$this->id = $id ?? bin2hex(random_bytes(8));
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function setId(string $id): void
	{
		$this->id = $id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getAttachment(): ?AttachmentEntity
	{
		return $this->attachment;
	}

	public function setAttachment(?AttachmentEntity $attachment): void
	{
		$this->attachment = $attachment;
	}
}
