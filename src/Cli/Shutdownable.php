<?php
declare(strict_types=1);

namespace Common\Cli;

interface Shutdownable
{
	public function isShutdownable(): bool;
}