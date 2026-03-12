<?php
declare(strict_types=1);

namespace Common\Dto\Save;

interface PreSaveDataManipulator
{
	public function handle(PreSaveDataManipulatorParams $params): PreSaveDataManipulatorResult;
}
