<?php
declare(strict_types=1);

namespace CommonTest\Dto\Save\Fixture\Thing;

use Common\Dto\BaseSaver;
use Common\Dto\Save\SaveConfig;

#[SaveConfig(postSave: RecordingPostSave::class)]
class SaverWithoutInitialDto extends BaseSaver
{
	protected function getKey(): string
	{
		return 'test--thing';
	}
}
