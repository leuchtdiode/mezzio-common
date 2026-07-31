<?php

namespace CommonTest\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class LeakEntity
{
	#[Id]
	#[GeneratedValue]
	#[Column(type: 'integer')]
	private int $id;

	#[Column(type: 'string')]
	private string $name;

	#[Column(type: 'string')]
	private string $city;

	#[Column(type: 'integer')]
	private int $amount;

	#[Column(type: 'datetime')]
	private DateTime $created;

	#[ManyToOne(targetEntity: LeakCompany::class)]
	#[JoinColumn(name: 'company_id', referencedColumnName: 'id')]
	private ?LeakCompany $company = null;
}
