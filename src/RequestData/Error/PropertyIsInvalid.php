<?php
namespace Common\RequestData\Error;

use Common\Error;
use Common\Hydration\ObjectToArrayHydratorProperty;
use Common\Translator;

class PropertyIsInvalid extends Error
{
	private function __construct(
		private readonly string $name,
		private readonly string $message
	)
	{
	}

	public static function create(string $name, string $message): self
	{
		return new self($name, $message);
	}

	#[ObjectToArrayHydratorProperty]
	public function getCode(): string
	{
		return 'PROPERTY_INVALID';
	}

	#[ObjectToArrayHydratorProperty]
	public function getMessage(): string
	{
		return sprintf(
			Translator::translate('%s ist ungültig (%s)'),
			$this->name,
			$this->message
		);
	}
}