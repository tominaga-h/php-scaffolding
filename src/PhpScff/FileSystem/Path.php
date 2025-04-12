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
	 * パスを結合し、新しいPathオブジェクトを返す
	 *
	 * @param string ...$paths
	 * @return self
	 */
	public function join(string ...$paths): self
	{
		$path = SymfonyPath::join($this->path, ...$paths);
		return new self($path);
	}
}
