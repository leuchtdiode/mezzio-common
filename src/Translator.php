<?php
namespace Common;

use Laminas\I18n\Translator\Translator as LaminasTranslator;

class Translator
{
	private static LaminasTranslator $instance;

	public static function setInstance(LaminasTranslator $translator)
	{
		self::$instance = $translator;
	}

	public static function getInstance(): LaminasTranslator
	{
		return self::$instance;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public static function translate(string $text)
	{
		return self::$instance->translate($text);
	}

	/**
	 * @return string
	 */
	public static function getLocale()
	{
		return self::$instance->getLocale();
	}

	/**
	 * @return string
	 */
	public static function getLanguage()
	{
		list($language) = explode('_', self::getLocale());

		return $language;
	}
}
