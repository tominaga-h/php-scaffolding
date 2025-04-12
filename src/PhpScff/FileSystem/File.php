<?php

namespace Hytmng\PhpScff\FileSystem;

use Symfony\Component\Filesystem\Filesystem;
use Hytmng\PhpScff\FileSystem\Path;

class File
{
	private Path $path;
	private Filesystem $fs;

	/**
	 * コンストラクタ
	 *
	 * @param Path $path
	 * @param Filesystem $fs
	 */
	public function __construct(Path $path, Filesystem $fs)
	{
		$this->path = $path;
		$this->fs = $fs;
	}

	/**
	 * ファイルパスからFileオブジェクトを作成する
	 *
	 * @param string $path
	 * @return self
	 */
	public static function fromPath(string $path): self
	{
		return new self(new Path($path), new Filesystem());
	}

	/**
	 * ファイルパスを取得する
	 */
	public function getFilePath(): string
	{
		return $this->path->get();
	}

	public function exists(): bool
	{
		return $this->fs->exists($this->path->get());
	}

}
