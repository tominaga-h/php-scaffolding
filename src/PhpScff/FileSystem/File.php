<?php

namespace Hytmng\PhpScff\FileSystem;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Hytmng\PhpScff\FileSystem\AbstractFileSystem;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\Exception\ExistenceException;

class File extends AbstractFileSystem
{
	/**
	 * Pathオブジェクトからオブジェクトを作成する
	 *
	 * @param Path $path
	 * @return self
	 */
	public static function fromPath(Path $path): self
	{
		return new self($path, new Filesystem());
	}
	/**
	 * ファイルパスからオブジェクトを作成する
	 *
	 * @param string $path
	 * @return self
	 */
	public static function fromStringPath(string $path): self
	{
		return new self(new Path($path), new Filesystem());
	}

	/**
	 * ファイル名を取得する
	 *
	 * @return string
	 */
	public function getFilename(): string
	{
		return $this->path->basename();
	}

	/**
	 * ファイルの内容を読み込む
	 *
	 * @return string
	 */
	public function read(): string
	{
		return $this->fs->readFile($this->path->get());
	}

	/**
	 * ファイルに内容を書き込む
	 *
	 * @param string $content
	 */
	public function write(string $content, bool $overwrite = false): void
	{
		if (
			!$this->exists() ||
			($this->exists() && $overwrite === true)
		) {
			$this->fs->dumpFile($this->path->get(), $content);
		} else {
			throw new ExistenceException('File "' . $this->path->get() . '" is already exists');
		}
	}

}
