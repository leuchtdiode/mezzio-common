<?php
declare(strict_types=1);

namespace Common\Console;

use Common\Translator\Initializer;

class GlobalTranslatorInitializer
{
	public function __construct(
		private readonly Initializer $initializer,
	)
	{
	}

	public function __invoke(): void
	{
		$this->initializer->initialize();
	}
}