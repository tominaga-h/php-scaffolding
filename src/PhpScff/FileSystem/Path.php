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
	 * 複数のパスからPathオブジェクトを生成する
	 *
	 * @param string ...$paths
	 * @return Path
	 */
	public static function from(string ...$paths): self
	{
		$path = SymfonyPath::join(...$paths);
		return new self($path);
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

	/**
	 * 現在のパスのbasenameを返す
	 *
	 * @return string
	 */
	public function basename(): string
	{
		return \basename($this->path);
	}

	/**
	 * 現在のパスのdirnameを返す
	 *
	 * @return string
	 */
	public function dirname(): string
	{
		return \dirname($this->path);
	}

	/**
	 * 親ディレクトリのパスに新しいパスを結合したパスを返す
	 *
	 * 例：
	 * `path/to/here` というパスの場合 `$path->replace('file')` を実行すると、
	 * `path/to/file` というパス（Pathオブジェクト）を返す
	 *
	 * @param string $path 新しいパス
	 * @return Path
	 */
	public function replace(string $path): self
	{
		$dirname = $this->dirname();
		return self::from($dirname, $path);
	}
}
