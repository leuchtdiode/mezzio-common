<?php
declare(strict_types=1);

namespace Common\Dto\Save;

class PreSaveDataManipulatorParams
{
	private array $data;

	public static function create(): static
	{
		return new static();
	}

	public function getData(): array
	{
		return $this->data;
	}

	public function setData(array $data): PreSaveDataManipulatorParams
	{
		$this->data = $data;
		return $this;
	}
}
