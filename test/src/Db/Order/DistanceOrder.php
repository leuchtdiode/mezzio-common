<?php

namespace CommonTest\Db\Order;

use Common\Db\Order\Distance;
use Override;

class DistanceOrder extends Distance
{
	#[Override] protected function getAlias(): string
	{
		return 't';
	}
}
