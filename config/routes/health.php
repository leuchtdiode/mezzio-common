<?php

use Common\Action\Health;
use Common\Router\HttpRouteCreator;

return HttpRouteCreator::create()
	->setRoute('/health')
	->setAction(Health::class);