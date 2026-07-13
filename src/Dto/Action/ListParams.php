<?php
declare(strict_types=1);

namespace Common\Dto\Action;

class ListParams
{
	private string $dtoKey;
	private array  $getParams;

	public static function create(): static
	{
		return new static();
	}

	public function getDtoKey(): string
	{
		return $this->dtoKey;
	}

	public function setDtoKey(string $dtoKey): ListParams
	{
		$this->dtoKey = $dtoKey;
		return $this;
	}

	public function getGetParams(): array
	{
		return $this->getParams;
	}

	public function setGetParams(array $getParams): ListParams
	{
		$this->getParams = $getParams;
		return $this;
	}
}
