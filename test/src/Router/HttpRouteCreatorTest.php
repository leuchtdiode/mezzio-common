<?php
namespace CommonTest\Router;

use Common\Router\FlattenRoutes;
use Common\Router\HttpRouteCreator;
use CommonTest\Base;

class HttpRouteCreatorTest extends Base
{
	public function test_route_creation(): void
	{
		$routes = FlattenRoutes::flatten([
			'user' => HttpRouteCreator::create()
				->setRoute('/user')
				->setAction('AnyNamespace\User\GetList')
				->setChildRoutes(
					[
						'single-item' => HttpRouteCreator::create()
							->setAction('AnyNamespace\User\Get')
							->setRoute('/:id')
							->setConstraints(
								[
									'id' => '.{36}',
								]
							)
							->setChildRoutes(
								[
									'remove' => HttpRouteCreator::create()
										->setAction('AnyNamespace\User\Remove')
										->setMethods([ 'DELETE' ]),
								]
							),
					]
				),
		]);

		$route1 = $routes['user.single-item.remove'];

		$this->assertEquals('/user/:id', $route1['path']);
		$this->assertContains('DELETE', $route1['allowed_methods']);
		$this->assertEquals('AnyNamespace\User\Remove', $route1['middleware']);
		$this->assertEquals('.{36}', $route1['options']['constraints']['id']);

		$route2 = $routes['user.single-item'];

		$this->assertEquals('/user/:id', $route2['path']);
		$this->assertContains('GET', $route2['allowed_methods']);
		$this->assertEquals('AnyNamespace\User\Get', $route2['middleware']);
		$this->assertEquals('.{36}', $route2['options']['constraints']['id']);

		$route3 = $routes['user'];

		$this->assertEquals('/user', $route3['path']);
		$this->assertContains('GET', $route3['allowed_methods']);
		$this->assertEquals('AnyNamespace\User\GetList', $route3['middleware']);
		$this->assertEmpty($route3['options']['constraints']);
	}
}
