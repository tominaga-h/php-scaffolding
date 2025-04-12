<?php

namespace Hytmng\PhpScff\FileSystem;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
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

	/**
	 * ファイルが存在するかどうかを確認する
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		return $this->fs->exists($this->path->get());
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
			throw new IOException('File ' . $this->path->get() . 'is already exists');
		}
	}

}
