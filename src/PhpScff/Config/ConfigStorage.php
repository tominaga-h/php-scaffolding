<?php

namespace Hytmng\PhpScff\Config;

use Hytmng\PhpScff\Template;
use Hytmng\PhpScff\Config\PathResolver;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\FileSystemInterface;
use Hytmng\PhpScff\Exception\ExistenceException;

class ConfigStorage
{
	private PathResolver $resolver;

	/**
	 * コンストラクタ
	 *
	 * @param string $path 設定フォルダのパス
	 * @param string $name 設定フォルダの名前
	 */
	public function __construct(string $path, string $name = '.phpscff')
	{
		$this->resolver = PathResolver::from($path, $name);
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
		return Directory::fromStringPath($this->resolver->getConfigDir());
	}

	/**
	 * テンプレートフォルダのDirectoryオブジェクトを返す
	 *
	 * @return Directory
	 */
	public function getTemplateDir(): Directory
	{
		return Directory::fromStringPath($this->resolver->getTemplateDir());
	}

	/**
	 * グループフォルダのDirectoryオブジェクトを返す
	 *
	 * @return Directory
	 */
	public function getGroupDir(): Directory
	{
		return Directory::fromStringPath($this->resolver->getGroupDir());
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
	 * @throws ExistenceException
	 */
	public function create(): void
	{
		if ($this->exists()) {
			throw new ExistenceException('Config directory is already exists');
		}

		// 設定フォルダの作成
		$this->getConfigDir()->create();

		// 下層フォルダの作成
		$this->getTemplateDir()->create();
		$this->getGroupDir()->create();
	}

	/**
	 * 設定フォルダを削除する
	 *
	 * @throws ExistenceException
	 */
	public function remove(): void
	{
		if (!$this->exists()) {
			throw new ExistenceException('Config directory is not exists');
		}

		// 下層フォルダの削除
		$this->getTemplateDir()->remove();
		$this->getGroupDir()->remove();

		// 設定フォルダの削除
		$this->getConfigDir()->remove();
	}

	/**
	 * テンプレートを追加する
	 *
	 * @param Template $template
	 * @throws ExistenceException
	 */
	public function addTemplate(Template $template): void
	{
		$filename = $template->getFilename();
		if ($this->hasTemplate($filename)) {
			throw new ExistenceException('Template "' . $filename . '" is already exists.');
		}

		$templateDir = $this->getTemplateDir();
		if ($templateDir->exists()) {
			$template->copy($templateDir->getStringPath());
		}
	}

	/**
	 * テンプレートを取得する
	 *
	 * @param string $name
	 * @return Template
	 * @throws ExistenceException
	 */
    public function getTemplate(string $name): Template
    {
        $filtered = $this->filterTemplate($name);
        if (\count($filtered) > 0) {
            return $filtered[0];
        } else {
            throw new ExistenceException('Template "' . $name . '" is not exists.');
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
		$filtered = \array_filter($files, function (FileSystemInterface $item) {
			return $item instanceof File;
		});
		return \array_map(function (File $item) {
			return Template::fromFile($item);
		}, $filtered);
	}

	/**
	 * テンプレートが存在するかどうかを確認する
	 *
	 * @param string|Template $template
	 * @return bool
	 */
	public function hasTemplate(string|Template $template): bool
	{
		if ($template instanceof Template) {
			$filename = $template->getFilename();
		} else {
			$filename = $template;
		}

		return \count($this->filterTemplate($filename)) > 0;
	}

	/**
	 * @param string $filename
	 * @return Template[]
	 */
	private function filterTemplate(string $filename): array
	{
		$templates = $this->getTemplates();
		$filtered = \array_filter($templates, function (Template $template) use ($filename) {
			return $template->getFilename() === $filename;
		});
		return \array_values($filtered);
	}

}
