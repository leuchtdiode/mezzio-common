<?php
namespace Common\RequestData\PropertyDefinition;

use Laminas\Validator\Date as DateValidator;

class DateTime extends PropertyDefinition
{
	public static function create(): self
	{
		return new self();
	}

	public function __construct()
	{
		parent::__construct();

		$this->addValidator(
			new DateValidator(
				[
					'format' => 'Y-m-d H:i:s'
				]
			)
		);
	}
}
