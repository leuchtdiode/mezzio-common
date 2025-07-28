<?php
declare(strict_types=1);

namespace Common\Translator;

use Common\Translator;
use Laminas\Translator\TranslatorInterface;

class Initializer
{
	public function __construct(
		private readonly array $config,
		private readonly TranslatorInterface $translator
	)
	{
	}

	public function initialize(): void
	{
		if ($this->config['common']['translator']['global']['enabled'])
		{
			Translator::setInstance($this->translator);

			setlocale(LC_TIME, $this->translator->getLocale());
		}
	}
}