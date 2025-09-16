<?php
declare(strict_types=1);

namespace Common\Retry;

class HandleParams
{
	private mixed $callable;
	private int   $tries;
	private int   $timeout;

	public static function create(): static
	{
	    return new static();
	}

	public function getCallable(): mixed
	{
		return $this->callable;
	}

	public function setCallable(mixed $callable): HandleParams
	{
		$this->callable = $callable;
		return $this;
	}

	public function getTries(): int
	{
		return $this->tries;
	}

	public function setTries(int $tries): HandleParams
	{
		$this->tries = $tries;
		return $this;
	}

	public function getTimeout(): int
	{
		return $this->timeout;
	}

	public function setTimeout(int $timeout): HandleParams
	{
		$this->timeout = $timeout;
		return $this;
	}
}