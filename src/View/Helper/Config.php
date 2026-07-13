<?php
namespace Common\View\Helper;

class Config
{
	private array $config;

	public function __construct(array $config)
	{
		$this->config = $config;
	}

	public function __invoke(): array
	{
		return $this->config;
	}
}
