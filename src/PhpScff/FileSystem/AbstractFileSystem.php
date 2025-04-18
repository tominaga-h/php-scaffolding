<?php

namespace Hytmng\PhpScff\FileSystem;

use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\FileSystem\PathTrait;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\FileSystemInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractFileSystem implements FileSystemInterface
{
	use PathTrait;

	protected Filesystem $fs;

	abstract public static function fromPath(Path $path): self;

	abstract public static function fromStringPath(string $path): self;

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
