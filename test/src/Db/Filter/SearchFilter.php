<?php

namespace CommonTest\Db\Filter;

use Common\Db\Filter\Generic;
use Override;

class SearchFilter extends Generic
{
	#[Override] protected function getColumns(): array
	{
		return [
			't.name' => self::LIKE,
			't.city' => self::STARTS_WITH,
		];
	}
}
