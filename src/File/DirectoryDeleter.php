<?php
declare(strict_types=1);

namespace Common\File;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DirectoryDeleter
{
	public function delete(string $dir): void
	{
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($files as $fileInfo)
		{
			$todo = ($fileInfo->isDir()
				? 'rmdir'
				: 'unlink');
			$todo($fileInfo->getRealPath());
		}

		rmdir($dir);
	}
}
