<?php
namespace Common\ActionPlugin;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;

class Redirect
{
	public function __construct(
		private readonly UrlHelper $urlHelper
	)
	{
	}

	public function __invoke(): self
	{
		return $this;
	}

	public function toRoute(string $routeName, int $status = 302): ResponseInterface
	{
		return new RedirectResponse(
			$this->urlHelper->generate($routeName),
			$status
		);
	}

	public function toUrl(string $url, int $status = 302): ResponseInterface
	{
		return new RedirectResponse($url, $status);
	}
}
