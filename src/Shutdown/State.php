<?php
declare(strict_types=1);

namespace Common\Shutdown;

readonly class State
{
	public function __construct(
		private array $config
	)
	{
	}

	public function markAsShuttingDown(): void
	{
		if (!file_exists($this->getFilePath()))
		{
			touch($this->getFilePath());
		}
	}

	public function isShuttingDown(): bool
	{
		return file_exists($this->getFilePath());
	}

	protected function getFilePath(): string
	{
		return $this->config['common']['shutdown']['state']['filePath'];
	}
}