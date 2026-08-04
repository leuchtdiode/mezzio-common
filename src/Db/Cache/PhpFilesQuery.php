<?php
declare(strict_types=1);

namespace Common\Db\Cache;

/**
 * Caches the DQL -> SQL translation.
 *
 * Only select this pool once the app builds stable DQL. Doctrine keys the cache by the DQL
 * string, so aliases or parameter names built with uniqid() add one entry per execution and
 * the pool grows without bounds - on disk, unlike the in memory adapters. Use
 * Common\Db\NameGenerator for aliases and parameter names, see CommonTest\Db\QueryCacheLeakTest.
 */
class PhpFilesQuery extends PhpFiles
{
	public function __construct()
	{
		parent::__construct('doctrine-query');
	}
}
