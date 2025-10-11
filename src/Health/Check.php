<?php
declare(strict_types=1);

namespace Common\Health;

interface Check
{
	public function check(): CheckResult;
}