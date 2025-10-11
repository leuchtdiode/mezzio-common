<?php
declare(strict_types=1);

namespace Common\Health;

use Common\Hydration\ArrayHydratable;
use Common\Hydration\ObjectToArrayHydratorProperty;

class CheckResult implements ArrayHydratable
{
	#[ObjectToArrayHydratorProperty]
	private string $key;

	#[ObjectToArrayHydratorProperty]
	private bool $healthy;

	/**
	 * @var string[]
	 */
	#[ObjectToArrayHydratorProperty]
	private array $messages = [];

	public function addMessage(string $message): void
	{
		$this->messages[] = $message;
	}

	public static function create(): static
	{
		return new static();
	}

	public function getKey(): string
	{
		return $this->key;
	}

	public function setKey(string $key): CheckResult
	{
		$this->key = $key;
		return $this;
	}

	public function isHealthy(): bool
	{
		return $this->healthy;
	}

	public function setHealthy(bool $healthy): CheckResult
	{
		$this->healthy = $healthy;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getMessages(): array
	{
		return $this->messages;
	}

	/**
	 * @param string[] $messages
	 */
	public function setMessages(array $messages): CheckResult
	{
		$this->messages = $messages;
		return $this;
	}
}