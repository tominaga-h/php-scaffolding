<?php

namespace Hytmng\PhpScff\FileSystem;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Hytmng\PhpScff\FileSystem\AbstractFileSystem;
use Hytmng\PhpScff\FileSystem\Path;

class File extends AbstractFileSystem
{
	/**
	 * ファイルパスからオブジェクトを作成する
	 *
	 * @param string $path
	 * @return self
	 */
	public static function fromPath(string $path): self
	{
		return new self(new Path($path), new Filesystem());
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
			throw new IOException('File "' . $this->path->get() . '" is already exists');
		}
	}

}
