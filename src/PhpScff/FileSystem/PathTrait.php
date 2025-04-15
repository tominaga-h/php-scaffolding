<?php

namespace Hytmng\PhpScff\FileSystem;

use Hytmng\PhpScff\FileSystem\Path;

trait PathTrait
{
	protected Path $path;

	/**
	 * Pathオブジェクトを取得する
	 *
	 * @return Path
	 */
	public function getPath(): Path
	{
		return $this->path;
	}

	/**
	 * パスを文字列で取得する
	 *
	 * @return string
	 */
	public function getStringPath(): string
	{
		return $this->path->get();
	}
}
