<?php

namespace CommonTest\Db\Filter;

use Common\Db\Filter\Number;
use Override;

class AmountFilter extends Number
{
	#[Override] protected function getField(): string
	{
		return 't.amount';
	}
}
