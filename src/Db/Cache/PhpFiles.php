<?php
declare(strict_types=1);

namespace Common\Db\Cache;

use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

/**
 * Base for the doctrine cache pools. The entries are written as plain PHP, so opcache keeps
 * them in shared memory and the app does not parse mapping attributes or DQL again on every
 * request.
 *
 * Roave\PsrContainerDoctrine\CacheFactory instantiates the configured class without arguments,
 * which is why every pool needs its own subclass with a fixed namespace.
 *
 * The entries never expire, so they have to be cleared on deploy.
 */
abstract class PhpFiles extends PhpFilesAdapter
{
	protected const string DIRECTORY = 'data/cache/doctrine';

	public function __construct(string $namespace)
	{
		parent::__construct($namespace, 0, static::DIRECTORY, true);
	}
}
