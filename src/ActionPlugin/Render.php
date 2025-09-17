<?php
declare(strict_types=1);

namespace Common\ActionPlugin;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\View\Model\ViewModel;
use Mezzio\Template\TemplateRendererInterface;

readonly class Render
{
	public function __construct(
		private TemplateRendererInterface $renderer,
	)
	{
	}

	public function viewModel(ViewModel $viewModel, int $status = 200, ?array $headers = null): HtmlResponse
	{
		return new HtmlResponse(
			html: $this->renderer->render($viewModel->getTemplate(), $viewModel->getVariables()),
			status: $status,
			headers: $headers ?? [],
		);
	}
}