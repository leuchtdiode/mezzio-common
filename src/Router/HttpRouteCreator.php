<?php
namespace Common\Router;

class HttpRouteCreator
{
	private ?string $route        = null;
	private ?string $action       = null;
	private bool    $mayTerminate = true;
	private ?array  $constraints  = null;
	private ?array  $childRoutes  = null;
	private array   $methods      = [];

	public static function create(): self
	{
		return new self();
	}

	public function setRoute(string $route): HttpRouteCreator
	{
		$this->route = $route;

		return $this;
	}

	public function setAction(string $action): HttpRouteCreator
	{
		$this->action = $action;

		return $this;
	}

	public function setMayTerminate(bool $mayTerminate): HttpRouteCreator
	{
		$this->mayTerminate = $mayTerminate;

		return $this;
	}

	public function setConstraints(array $constraints): HttpRouteCreator
	{
		$this->constraints = $constraints;

		return $this;
	}

	public function setChildRoutes(array $childRoutes): HttpRouteCreator
	{
		$this->childRoutes = $childRoutes;

		return $this;
	}

	public function setMethods(array $methods): HttpRouteCreator
	{
		$this->methods = $methods;

		return $this;
	}

	/**
	 * @deprecated omit getConfig() in route definitions
	 *
	 * Laminas returned an array here. Now for Mezzio we just return the instance, so we do not have to remove
	 * all getConfig() calls
	 */
	public function getConfig(): static
	{
		return $this;
	}

	/**
	 * @return HttpRouteCreator[]|null
	 */
	public function getChildRoutes(): ?array
	{
		return $this->childRoutes;
	}

	public function getMethods(): array
	{
		return $this->methods;
	}

	public function getAction(): ?string
	{
		return $this->action;
	}

	public function getRoute(): ?string
	{
		return $this->route;
	}

	public function getConstraints(): ?array
	{
		return $this->constraints;
	}

	public function isMayTerminate(): bool
	{
		return $this->mayTerminate;
	}
}
