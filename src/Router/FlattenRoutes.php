<?php
declare(strict_types=1);

namespace Common\Router;

class FlattenRoutes
{
	/**
	 * @var array<string, array>
	 */
	private static array $routes;

	/**
	 * @param HttpRouteCreator[] $routes
	 * @return array<string, array>
	 */
	public static function flatten(array $routes): array
	{
		self::$routes = [];

		self::processRoutes($routes);

		return self::$routes;
	}

	/**
	 * @param string[] $parentNames
	 */
	private static function buildName(string $name, array $parentNames): string
	{
		$fullName = '';

		foreach ($parentNames as $i => $parentName)
		{
			$fullName .= ($i > 0)
				? '.'
				: '';

			$fullName .= $parentName;
		}

		$fullName .= ($fullName
				? '.'
				: '') . $name;

		return $fullName;
	}

	/**
	 * @param HttpRouteCreator[] $parents
	 */
	private static function buildPath(HttpRouteCreator $route, array $parents): string
	{
		$fullPath = '';

		foreach ($parents as $parent)
		{
			$fullPath .= $parent->getRoute();
		}

		$fullPath .= $route->getRoute();

		return $fullPath;
	}

	/**
	 * @param HttpRouteCreator[] $routes
	 * @param HttpRouteCreator[] $parents
	 * @param string[] $parentNames
	 */
	private static function processRoutes(
		array $routes,
		array $parents = [],
		array $parentNames = [],
	): void
	{
		foreach ($routes as $name => $route)
		{
			$innerParents     = $parents;
			$innerParentNames = $parentNames;

			$childRoutes = $route->getChildRoutes();

			if ($childRoutes)
			{
				$innerParents[]     = $route;
				$innerParentNames[] = $name;

				self::processRoutes($childRoutes, $innerParents, $innerParentNames);
			}

			if (($route->isMayTerminate() ?? true) && $route->getAction())
			{
				self::$routes[self::buildName($name, $parentNames)] = [
					'path'            => self::buildPath($route, $parents),
					'allowed_methods' => $route->getMethods()
						?: [ 'GET' ],
					'middleware'      => $route->getAction(),
					'options'         => [
						'constraints' => self::buildConstraints($route, $parents),
					],
				];
			}
		}
	}

	/**
	 * @param HttpRouteCreator[] $parents
	 * @return array<string, string>
	 */
	private static function buildConstraints(HttpRouteCreator $route, array $parents): array
	{
		$constraints = [];

		foreach ($parents as $parent)
		{
			if (($parentConstraints = $parent->getConstraints()))
			{
				$constraints = array_merge($constraints, $parentConstraints);
			}
		}

		if (($routeConstraints = $route->getConstraints()))
		{
			$constraints = array_merge($constraints, $routeConstraints);
		}

		return $constraints;
	}
}