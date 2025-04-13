<?php

namespace Hytmng\PhpScff\FileSystem;

use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\FileSystemInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractFileSystem implements FileSystemInterface
{
	protected Path $path;
	protected Filesystem $fs;

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
	 * パスを取得する
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path->get();
	}

	/**
	 * ディレクトリかどうかを確認する
	 *
	 * @return bool
	 */
	public function isDir(): bool
	{
		return $this instanceof Directory;
	}

	/**
	 * ファイルかどうかを確認する
	 *
	 * @return bool
	 */
	public function isFile(): bool
	{
		return $this instanceof File;
	}

	/**
	 * ファイルが存在するかどうかを確認する
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		return $this->fs->exists($this->path->get());
	}
}
