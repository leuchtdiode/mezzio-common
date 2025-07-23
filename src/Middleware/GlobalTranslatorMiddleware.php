<?php
declare(strict_types=1);

namespace Common\Middleware;

use Common\Translator;
use Laminas\Translator\TranslatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GlobalTranslatorMiddleware implements MiddlewareInterface
{
	public function __construct(
		private readonly array $config,
		private readonly TranslatorInterface $translator
	)
	{
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if ($this->config['common']['translator']['global']['enabled'])
		{
			Translator::setInstance($this->translator);

			setlocale(LC_TIME, $this->translator->getLocale());
		}

		return $handler->handle($request);
	}
}