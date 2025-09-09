<?php
declare(strict_types=1);

namespace Common\Cli;

interface HealthCheck
{
	public function isHealthy(): bool;
}