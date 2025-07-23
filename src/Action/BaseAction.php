<?php
namespace Common\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class BaseAction implements RequestHandlerInterface
{
	private ServerRequestInterface $request;

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$this->request = $request;

		return $this->executeAction();
	}

	abstract public function executeAction();

	protected function getRequest(): ServerRequestInterface
	{
		return $this->request;
	}
}
