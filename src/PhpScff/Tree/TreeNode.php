<?php

namespace Hytmng\PhpScff\Tree;

use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Path;

/**
 * ツリー構造を表現するための `File` クラスのラッパークラス
 */
class TreeNode
{
	private File $file;

	public function __construct(File $file)
	{
		$this->file = $file;
	}

	/**
	 * パスから `TreeNode` を生成する
	 *
	 * @param Path $path
	 * @return TreeNode
	 */
	public static function fromPath(Path $path): TreeNode
	{
		$file = File::fromPath($path);
		return new TreeNode($file);
	}

	public function create(string $content): void
	{
		$this->file->write($content);
	}

	public function __toString(): string
	{
		return $this->file->getFilename();
	}
}
