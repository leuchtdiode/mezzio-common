<?php
namespace Common\File;

class File
{
	private string $name;
	private int    $size;
	private string $mimeType;
	private string $content;

	public static function create(): self
	{
		return new self();
	}

	public static function createFromArray(array $data): self
	{
		$file = new self();
		$file->setName($data['name']);
		$file->setMimeType($data['mimeType']);
		$file->setSize($data['size']);
		$file->setContent(
			base64_decode($data['content'])
		);

		return $file;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): File
	{
		$this->name = $name;
		return $this;
	}

	public function getSize(): int
	{
		return $this->size;
	}

	public function setSize(int $size): File
	{
		$this->size = $size;
		return $this;
	}

	public function getMimeType(): string
	{
		return $this->mimeType;
	}

	public function setMimeType(string $mimeType): File
	{
		$this->mimeType = $mimeType;
		return $this;
	}

	public function getContent(): string
	{
		return $this->content;
	}

	public function setContent(string $content): File
	{
		$this->content = $content;
		return $this;
	}
}
