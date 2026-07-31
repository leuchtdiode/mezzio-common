<?php

namespace CommonTest\Db\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class LeakCompany
{
	#[Id]
	#[GeneratedValue]
	#[Column(type: 'integer')]
	private int $id;

	#[Column(type: 'string')]
	private string $name;
}
