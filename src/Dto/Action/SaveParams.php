<?php
declare(strict_types=1);

namespace Common\Dto\Action;

use Psr\Http\Message\ServerRequestInterface;

class SaveParams
{
	private string                 $dtoKey;
	private ServerRequestInterface $request;
	private ?string                $id = null;

	public static function create(): static
	{
		return new static();
	}

	public function getDtoKey(): string
	{
		return $this->dtoKey;
	}

	public function setDtoKey(string $dtoKey): SaveParams
	{
		$this->dtoKey = $dtoKey;
		return $this;
	}

	public function getId(): ?string
	{
		return $this->id;
	}

	public function setId(?string $id): SaveParams
	{
		$this->id = $id;
		return $this;
	}

	public function getRequest(): ServerRequestInterface
	{
		return $this->request;
	}

	public function setRequest(ServerRequestInterface $request): SaveParams
	{
		$this->request = $request;
		return $this;
	}
}
