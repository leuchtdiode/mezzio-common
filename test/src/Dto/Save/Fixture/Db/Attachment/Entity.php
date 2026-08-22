<?php
declare(strict_types=1);

namespace CommonTest\Dto\Save\Fixture\Db\Attachment;

use Common\Db\Entity as DbEntity;
use Common\Dto\EntityConfig;
use Common\Dto\PropertyConfig;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'fixture_attachment')]
#[ORM\Entity(repositoryClass: Repository::class)]
#[EntityConfig(dtoKey: 'test--attachment')]
class Entity implements DbEntity
{
	#[ORM\Id]
	#[ORM\Column(type: 'string')]
	#[PropertyConfig(validationLabel: 'ID')]
	private string $id;

	#[ORM\Column(type: 'string')]
	#[PropertyConfig(validationLabel: 'File name')]
	private string $fileName = '';

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

	public function getFileName(): string
	{
		return $this->fileName;
	}

	public function setFileName(string $fileName): void
	{
		$this->fileName = $fileName;
	}
}
