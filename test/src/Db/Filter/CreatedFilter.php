<?php

namespace CommonTest\Db\Filter;

use Common\Db\Filter\Date;
use Override;

class CreatedFilter extends Date
{
	#[Override] protected function getColumn(): string
	{
		return 't.created';
	}
}
