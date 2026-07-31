<?php

namespace CommonTest\Db\Filter;

use Common\Db\Filter\Equals;
use Override;

class NameFilter extends Equals
{
	#[Override] protected function getField(): string
	{
		return 't.name';
	}
}
