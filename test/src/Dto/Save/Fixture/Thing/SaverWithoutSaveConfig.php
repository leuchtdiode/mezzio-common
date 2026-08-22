<?php
declare(strict_types=1);

namespace CommonTest\Dto\Save\Fixture\Thing;

use Common\Dto\BaseSaver;

class SaverWithoutSaveConfig extends BaseSaver
{
	protected function getKey(): string
	{
		return 'test--thing';
	}
}
