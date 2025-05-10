<?php

namespace Hytmng\PhpScff;

use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\FileSystem\PathTrait;
use Symfony\Component\Filesystem\Filesystem;
use Hytmng\PhpScff\Process\EditProcess;

class Template
{
	use PathTrait;

	private File $file;
	private Filesystem $filesystem;
	private ?EditProcess $editProcess = null;

	public function __construct(File $file, Filesystem $filesystem)
	{
		$this->file = $file;
		$this->path = $file->getPath();
		$this->filesystem = $filesystem;
	}

	/**
	 * FileオブジェクトからTemplateオブジェクトを生成する
	 *
	 * @param File $file
	 * @return Template
	 */
	public static function fromFile(File $file): self
	{
		return new self($file, new Filesystem());
	}

	/**
	 * PathオブジェクトからTemplateオブジェクトを生成する
	 *
	 * @param Path $path
	 * @return Template
	 */
	public static function fromPath(Path $path): self
	{
		$file = File::fromPath($path);
		return new self($file, new Filesystem());
	}

	/**
	 * パスからTemplateオブジェクトを生成する
	 *
	 * @param string $path パス
	 * @return Template
	 */
	public static function fromStringPath(string $path): self
	{
		$file = File::fromStringPath($path);
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
		$destPath = Path::from($dest, $this->getFilename());
		$this->filesystem->copy($this->path->get(), $destPath->get());
	}

	/**
	 * EditProcessを設定する
	 *
	 * @param EditProcess $editProcess
	 */
	public function setEditProcess(EditProcess $editProcess): void
	{
		$this->editProcess = $editProcess;
	}

	/**
	 * テンプレートを編集する
	 *
	 * @return bool 編集が成功した場合は true, 失敗した場合は false
	 */
	public function edit(): bool
	{
		$editor = $this->editProcess ?? new EditProcess();
		return $editor->edit($this->path->get());
	}
}
