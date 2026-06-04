<?php
namespace Common\RequestData\Error;

use Common\Error;
use Common\Hydration\ObjectToArrayHydratorProperty;
use Common\Translator;

class PropertyIsMandatory extends Error
{
	private function __construct(
		private readonly string $name
	)
	{
	}

	public static function create(string $name): self
	{
		return new self($name);
	}

	#[ObjectToArrayHydratorProperty]
	public function getCode(): string
	{
		return 'MANDATORY_PROPERTY_MISSING';
	}

	#[ObjectToArrayHydratorProperty]
	public function getMessage(): string
	{
		return sprintf(
			Translator::translate('%s darf nicht leer sein'),
			$this->name
		);
	}
}