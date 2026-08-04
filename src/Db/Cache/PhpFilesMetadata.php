<?php
declare(strict_types=1);

namespace Common\Db\Cache;

/**
 * Without a metadata cache the mapping attributes of every entity are parsed again on each
 * request. Safe to use in any app, the entries are keyed by entity class name.
 */
class PhpFilesMetadata extends PhpFiles
{
	public function __construct()
	{
		parent::__construct('doctrine-metadata');
	}
}
