<?php
declare(strict_types=1);

namespace Common\Action;

use Psr\Http\Message\ServerRequestInterface;

readonly class ExecuteActionParams
{
	public function __construct(
		private ServerRequestInterface $request
	)
	{
	}

	public function getRequest(): ServerRequestInterface
	{
		return $this->request;
	}
}