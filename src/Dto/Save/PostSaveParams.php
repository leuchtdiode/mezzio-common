<?php
declare(strict_types=1);

namespace Common\Dto\Save;

use Common\Dto\Dto;

class PostSaveParams
{
	private Dto  $dto;
	private bool $addition;
	private ?Dto $initialDto = null;

	public static function create(): static
	{
		return new static();
	}

	public function getDto(): Dto
	{
		return $this->dto;
	}

	public function setDto(Dto $dto): PostSaveParams
	{
		$this->dto = $dto;
		return $this;
	}

	public function isAddition(): bool
	{
		return $this->addition;
	}

	public function setAddition(bool $addition): PostSaveParams
	{
		$this->addition = $addition;
		return $this;
	}

	/**
	 * State of the dto before the save happened. Only available if the saver is annotated with
	 * #[SaveConfig(provideInitialDto: true)] and the save was an update, null otherwise.
	 */
	public function getInitialDto(): ?Dto
	{
		return $this->initialDto;
	}

	public function setInitialDto(?Dto $initialDto): PostSaveParams
	{
		$this->initialDto = $initialDto;
		return $this;
	}
}
