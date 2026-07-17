<?php
namespace Common\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class BaseAction implements RequestHandlerInterface
{
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		return $this->executeAction(
			new ExecuteActionParams(request: $request)
		);
	}

	abstract public function executeAction(ExecuteActionParams $params): ResponseInterface;
}
