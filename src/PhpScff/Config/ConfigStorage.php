<?php

namespace Hytmng\PhpScff\Config;

use Hytmng\PhpScff\Config\PathResolver;

class ConfigStorage
{

	private PathResolver $path;

	/**
	 * コンストラクタ
	 *
	 * @param string $path 設定フォルダのパス
	 * @param string $name 設定フォルダの名前
	 */
	public function __construct(string $path, string $name = '.phpscff')
	{
		$this->path = PathResolver::from($path, $name);
	}

	/**
	 * PathResolverを設定する
	 *
	 * @param PathResolver $path
	 */
	public function setPathResolver(PathResolver $path): void
	{
		$this->path = $path;
	}

	public function loadTemplates()
	{

	}
}
