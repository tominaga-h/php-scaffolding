<?php

namespace Hytmng\PhpScff\FileSystem;

use IteratorAggregate;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use Symfony\Component\Filesystem\Filesystem;
use Hytmng\PhpScff\FileSystem\AbstractFileSystem;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\FileSystem\Helper;
use Hytmng\PhpScff\FileSystem\FileSystemInterface;
use Hytmng\PhpScff\Exception\ExistenceException;

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
	 *
	 * @throws ExistenceException
	 */
	public function create(int $mode = 0777): void
	{
		if (!$this->exists()) {
			$this->fs->mkdir($this->path->get(), $mode);
		} else {
			throw new ExistenceException('Directory "' . $this->path->get() . '" is already exists');
		}
	}

	/**
	 * ディレクトリを削除する
	 *
	 * @throws ExistenceException
	 */
	public function remove(): void
	{
		if (!$this->exists()) {
			throw new ExistenceException('Directory "' . $this->path->get() . '" is not exists');
		}

		$this->fs->remove($this->path->get());
	}

	/**
	 * ディレクトリの内容をオブジェクトにした配列を返す
	 *
	 * @param bool $recursive trueにすると再帰的にディレクトリの内容を取得する
	 * @return FileSystemInterface[]
	 * @throws ExistenceException
	 */
	public function list(bool $recursive = false): array
	{
		if (!$this->exists()) {
			throw new ExistenceException('Directory "' . $this->path->get() . '" is not exists');
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

	/**
	 * サブディレクトリのパスを取得する
	 *
	 * @param string ...$dirs サブディレクトリ名
	 * @return Path
	 */
	public function getSubDirPath(string ...$dirs): Path
	{
		return $this->path->join(...$dirs);
	}

	/**
	 * サブディレクトリのDirectoryオブジェクトを取得する
	 *
	 * @param string ...$dirs サブディレクトリ名
	 * @return self
	 */
	public function getSubDir(string ...$dirs): self
	{
		return self::fromPath($this->getSubDirPath(...$dirs));
	}

	/**
	 * ディレクトリ内のファイルパスを取得する
	 *
	 * @param string $filename ファイル名
	 * @return Path
	 */
	public function getFilePath(string $filename): Path
	{
		return $this->path->join($filename);
	}

	/**
	 * ディレクトリ内のファイルオブジェクトを取得する
	 *
	 * @param string $filename ファイル名
	 * @return File
	 */
	public function getFile(string $filename): File
	{
		return File::fromPath($this->getFilePath($filename));
	}

}
