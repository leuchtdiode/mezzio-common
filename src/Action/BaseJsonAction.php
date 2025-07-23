<?php
namespace Common\Action;

use Psr\Http\Message\ResponseInterface;

abstract class BaseJsonAction extends BaseAction
{
	abstract public function executeAction(): ResponseInterface;
}