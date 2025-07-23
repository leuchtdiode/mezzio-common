<?php
namespace CommonTest;

use Common\Translator;
use Laminas\I18n\Translator\Translator as LaminasI18nTranslator;
use Testing\BaseTestCase;

class Base extends BaseTestCase
{
	protected function isDatabaseNecessary(): bool
	{
		return false;
	}

	protected function setDummyTranslator(): void
	{
		$translator = new LaminasI18nTranslator();

		Translator::setInstance($translator);
	}
}
