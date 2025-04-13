<?php

namespace Hytmng\PhpScff\FileSystem;

use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\FileSystemInterface;

class Helper
{
	/**
	 * パスからFileオブジェクトかDirectoryオブジェクトを返す
	 *
	 * @param string $path
	 * @return FileSystemInterface
	 * @throws \InvalidArgumentException
	 */
	public static function convertObject(string $path): FileSystemInterface
	{
		if (\is_dir($path)) {
			return Directory::fromPath($path);
		} else if (\is_file($path)) {
			return File::fromPath($path);
		} else {
			throw new \InvalidArgumentException('Invalid path: ' . $path);
		}
	}
}
