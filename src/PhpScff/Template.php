<?php

namespace Hytmng\PhpScff;

use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Path;
use Symfony\Component\Filesystem\Filesystem;

class Template
{
	private File $file;
	private Filesystem $filesystem;

	public function __construct(File $file, Filesystem $filesystem)
	{
		$this->file = $file;
		$this->filesystem = $filesystem;
	}

	/**
	 * パスからTemplateオブジェクトを生成する
	 *
	 * @param string $path パス
	 * @return Template
	 */
	public static function fromPath(string $path): self
	{
		$file = File::fromPath($path);
		return new self($file, new Filesystem());
	}

	/**
	 * ファイル名を取得する
	 *
	 * @return string
	 */
	public function getFilename(): string
	{
		return $this->file->getFilename();
	}

	/**
	 * テンプレートをコピーする
	 *
	 * @param string $dest コピー先のパス
	 */
	public function copy(string $dest): void
	{
		$this->filesystem->copy($this->file->getPath(), $dest);
	}
}
