<?php

namespace Hytmng\PhpScff\Config;

use Hytmng\PhpScff\Template;
use Hytmng\PhpScff\Config\PathResolver;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Directory;
use Symfony\Component\Filesystem\Exception\IOException;

class ConfigStorage
{

	private Path $path;
	private PathResolver $resolver;
	private array $templates = [];

	/**
	 * コンストラクタ
	 *
	 * @param string $path 設定フォルダのパス
	 * @param string $name 設定フォルダの名前
	 */
	public function __construct(string $path, string $name = '.phpscff')
	{
		$this->resolver = PathResolver::from($path, $name);
		// $this->directory = Directory::fromPath($this->resolver->getConfigDir());
		$this->path = $this->resolver->getPath();
		$this->templates = [];
	}

	/**
	 * PathResolverを設定する
	 *
	 * @param PathResolver $resolver
	 */
	public function setPathResolver(PathResolver $resolver): void
	{
		$this->resolver = $resolver;
	}

	/**
	 * 設定フォルダのDirectoryオブジェクトを返す
	 *
	 * @return Directory
	 */
	public function getConfigDir(): Directory
	{
		return Directory::fromPath($this->resolver->getConfigDir());
	}

	/**
	 * テンプレートフォルダのDirectoryオブジェクトを返す
	 *
	 * @return Directory
	 */
	public function getTemplateDir(): Directory
	{
		return Directory::fromPath($this->resolver->getTemplateDir());
	}

	/**
	 * グループフォルダのDirectoryオブジェクトを返す
	 *
	 * @return Directory
	 */
	public function getGroupDir(): Directory
	{
		return Directory::fromPath($this->resolver->getGroupDir());
	}

	/**
	 * 設定フォルダが存在するかどうかを確認する
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		return $this->getConfigDir()->exists();
	}

	/**
	 * 設定フォルダを作成する
	 *
	 * @throws IOException
	 */
	public function create(): void
	{
		if ($this->exists()) {
			throw new IOException('Config directory is already exists');
		}

		// 設定フォルダの作成
		$this->getConfigDir()->create();

		// 下層フォルダの作成
		$paths = $this->resolver->getDirsInConfigDir();
		foreach ($paths as $path) {
			$dir = Directory::fromPath($path);
			$dir->create();
		}
	}

	/**
	 * テンプレートを追加する
	 *
	 * @param Template $template
	 */
	public function addTemplate(Template $template): void
	{
		$this->templates[] = $template;
		if ($this->getTemplateDir()->exists()) {
			$template->copy($this->getTemplateDir()->getPath());
		}
	}

	/**
	 * テンプレートを取得する
	 *
	 * @return Template[]
	 */
	public function getTemplates(): array
	{
		$files = $this->getTemplateDir()->list(true);
		$this->templates = \array_map(function (File|Directory $item) {
			return Template::fromPath($item->getPath());
		}, $files);
		return $this->templates;
	}

}
