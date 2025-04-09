<?php

namespace Hytmng\PhpScff;

/**
 * 設定フォルダの管理をするクラス
 */
class Config
{
	// 設定フォルダのパス
	private string $configDir;

	// 設定フォルダ内のディレクトリ
	private const DIRECTORIES = [
		'templates',
		'groups',
	];

	/**
	 * コンストラクタ
	 *
	 * @param string $dirname 設定フォルダ名
	 * @param string $directory 設定フォルダのパス
	 */
	public function __construct(string $dirname, string $directory)
	{
		$directory = rtrim($directory, DIRECTORY_SEPARATOR);
		$this->setConfigDir($this->joinPath($directory, $dirname));
	}

	/**
	 * ディレクトリとファイルからパスを作成する
	 *
	 * @param string $directory ディレクトリ
	 * @param string $file ファイル
	 * @return string パス
	 */
	protected function joinPath(string $directory, string $file): string
	{
		return $directory . DIRECTORY_SEPARATOR . $file;
	}

	/**
	 * 設定フォルダのパスを設定する
	 *
	 * @param string $path 設定フォルダのパス
	 */
	public function setConfigDir(string $path): void
	{
		$this->configDir = $path;
	}

	/**
	 * 設定フォルダのパスを返す
	 *
	 * @return string 設定フォルダのパス
	 */
	public function getConfigDir(): string
	{
		return $this->configDir;
	}

	/**
	 * 設定フォルダが存在するかどうかを返す
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		return \is_dir($this->configDir);
	}

	/**
	 * 設定フォルダが存在していなければ作成する
	 */
	public function create(): void
	{
		if ($this->exists()) {
			return;
		}

		// 設定フォルダの作成
		\mkdir($this->configDir, 0777, true);
		// 各ディレクトリの作成
		foreach (self::DIRECTORIES as $directory) {
			$path = $this->joinPath($this->configDir, $directory);
			\mkdir($path, 0777, true);
		}
	}

	/**
	 * テンプレートディレクトリ内のテンプレートを取得する
	 *
	 * @return Template[]
	 */
	public function getTemplates(): array
	{
		$templateDir = $this->joinPath($this->configDir, 'templates');
		$items = scandir($templateDir) ?: [];
		$templates = [];

		foreach ($items as $name) {
			if ($name === '.' || $name === '..') {
				continue;
			}

			$templatePath = $this->joinPath($templateDir, $name);
			$templates[] = new Template($templatePath);
		}
		return $templates;
	}
}
