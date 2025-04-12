<?php

namespace Hytmng\PhpScff\FileSystem;

use Symfony\Component\Filesystem\Path as SymfonyPath;

class Path
{
	private string $path;

	public function __construct(string $path)
	{
		$this->path = $path;
	}

	/**
	 * パスを取得する
	 *
	 * @return string
	 */
	public function get(): string
	{
		return $this->path;
	}

	/**
	 * パスを結合する
	 *
	 * @param string ...$paths
	 * @return string
	 */
	public function join(string ...$paths): string
	{
		return SymfonyPath::join($this->path, ...$paths);
	}
}
