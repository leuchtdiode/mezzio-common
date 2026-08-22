<?php
declare(strict_types=1);

namespace CommonTest\Dto\Save\Fixture\Thing;

use Common\Dto\Save\PostSave;
use Common\Dto\Save\PostSaveParams;
use Common\Dto\Save\PostSaveResult;

/**
 * Keeps the params it was handed so the test can inspect what BaseSaver passed in.
 */
class RecordingPostSave implements PostSave
{
	private ?PostSaveParams $params = null;

	public function handle(PostSaveParams $params): PostSaveResult
	{
		$this->params = $params;

		$result = new PostSaveResult();
		$result->setDto($params->getDto());

		return $result;
	}

	public function getParams(): ?PostSaveParams
	{
		return $this->params;
	}
}
