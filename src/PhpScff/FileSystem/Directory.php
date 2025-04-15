<?php

namespace Hytmng\PhpScff\FileSystem;

use IteratorAggregate;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Hytmng\PhpScff\FileSystem\AbstractFileSystem;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\FileSystem\Helper;
use Hytmng\PhpScff\FileSystem\FileSystemInterface;

class Directory extends AbstractFileSystem implements IteratorAggregate
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
	 * ディレクトリを作成する
	 */
	public function create(int $mode = 0777): void
	{
		if (!$this->exists()) {
			$this->fs->mkdir($this->path->get(), $mode);
		} else {
			throw new IOException('Directory "' . $this->path->get() . '" is already exists');
		}
	}

	/**
	 * ディレクトリを削除する
	 *
	 * @throws IOException
	 */
	public function remove(): void
	{
		if (!$this->exists()) {
			throw new IOException('Directory "' . $this->path->get() . '" is not exists');
		}

		$this->fs->remove($this->path->get());
	}

	/**
	 * ディレクトリの内容をオブジェクトにした配列を返す
	 *
	 * @param bool $recursive trueにすると再帰的にディレクトリの内容を取得する
	 * @return FileSystemInterface[]
	 */
	public function list(bool $recursive = false): array
	{
		if (!$this->exists()) {
			throw new IOException('Directory "' . $this->path->get() . '" is not exists');
		}

		if ($recursive) {
			$iter = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
					$this->path->get(),
					FileSystemIterator::SKIP_DOTS
				),
			);
		} else {
			$iter = new FileSystemIterator(
				$this->path->get(),
				FileSystemIterator::SKIP_DOTS
			);
		}

		$list = [];
		foreach ($iter as $path) {
			$list[] = Helper::convertObject((string)$path);
		}
		return $list;
	}

	/**
	 * ディレクトリの内容を返す
	 *
	 * @return \Traversable
	 */
	public function getIterator(): \Traversable
	{
		return new \ArrayIterator($this->list());
	}

}
