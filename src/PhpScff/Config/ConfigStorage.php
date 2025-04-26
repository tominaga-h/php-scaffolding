<?php

namespace Hytmng\PhpScff\Config;

use Hytmng\PhpScff\Template;
use Hytmng\PhpScff\Config\PathResolver;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\Path;
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

		// 設定フォルダの削除
		$this->getConfigDir()->remove();
	}

    /**
     * テンプレートを追加する
     *
     * @param Template   $template テンプレートオブジェクト
     * @param string|null $group    指定するとそのグループ内にテンプレートを追加する
     * @throws ExistenceException
     */
    public function addTemplate(Template $template, ?string $group = null): void
	{
		$filename = $template->getFilename();
        if ($this->hasTemplate($filename, $group)) {
            throw new ExistenceException('Template "' . $filename . '" is already exists.');
        }

        $templateDir = $this->getTemplateDir();
        if (!\is_null($group)) {
            $groupDir = $templateDir->getSubDir($group);
			// グループの存在は任意
            if (!$groupDir->exists()) {
                $groupDir->create();
            }
            $templateDir = $groupDir;
        }
        if ($templateDir->exists()) {
            $template->copy($templateDir->getStringPath());
        }
	}

	/**
	 * テンプレートを取得する
	 *
	 * @param string      $name テンプレートファイル名
	 * @param string|null $group 指定するとそのグループ内のテンプレートを取得する
	 * @return Template
	 * @throws ExistenceException
	 */
    public function getTemplate(string $name, ?string $group = null): Template
    {
		$templates = $this->getTemplates($group);
		$filtered = \array_filter($templates, function (Template $template) use ($name) {
			return $template->getFilename() === $name;
		});
        if (\count($filtered) > 0) {
            return $filtered[0];
        } else {
            throw new ExistenceException('Template "' . $name . '" is not exists.');
        }
    }

	/**
	 * すべてのテンプレートを取得する
	 *
	 * @param string|null $group 指定するとそのグループ内のテンプレートのみを取得する
	 * @return Template[]
	 * @throws ExistenceException グループディレクトリがない場合
	 */
	public function getTemplates(?string $group = null): array
	{
		$templateDir = $this->getTemplateDir();
		if (!\is_null($group)) {
			$groupDir = $templateDir->getSubDir($group);
			// グループの存在は必須
			if (!$groupDir->exists()) {
				throw new ExistenceException('Group "' . $group . '" is not exists');
			}
			$files = $groupDir->list(false); // グループ内のファイルのみ
		} else {
			$files = $templateDir->list(true); // テンプレートフォルダ内のファイルすべて
		}

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
     * @param string|Template $template テンプレート名またはTemplateオブジェクト
     * @param string|null     $group    指定するとそのグループ内のテンプレートを対象とする
     * @return bool
     * @throws ExistenceException
     */
    public function hasTemplate(string|Template $template, ?string $group = null): bool
	{
		if ($template instanceof Template) {
			$filename = $template->getFilename();
		} else {
			$filename = $template;
		}

        $templateDir = $this->getTemplateDir();
        if (!\is_null($group)) {
            $directory = $templateDir->getSubDir($group);
			// グループの存在は任意
            if (!$directory->exists()) {
                return false;
            }
        } else {
            $directory = $templateDir;
            if (!$directory->exists()) {
                throw new ExistenceException('Directory "' . $directory->getStringPath() . '" is not exists');
            }
        }
        // 指定ディレクトリ内のファイル存在を直接チェック
        return $directory->getFile($filename)->exists();
	}

	/**
	 * 全てのグループとそのテンプレートの一覧を取得する
	 *
	 * @return array<string, Template[]> グループ名をキー、テンプレートオブジェクトの配列を値とする連想配列
	 */
	public function getTemplatesByGroup(): array
	{
		$items = $this->getTemplateDir()->list(false);
		$groups = [];
		foreach ($items as $item) {
			if ($item instanceof Directory) {
				$groupName = $item->getDirName();
				$templates = $this->getTemplates($groupName);
				if (!empty($templates)) {
					$groups[$groupName] = $templates;
				}
			}
		}
		return $groups;
	}

	/**
	 * 全てのグループ名を取得する
	 *
	 * @return string[] グループ名の配列
	 */
	public function getGroups(): array
	{
		$items = $this->getTemplateDir()->list(false);
		$groups = [];
		foreach ($items as $item) {
			if ($item instanceof Directory) {
				$groups[] = $item->getDirName();
			}
		}
		return $groups;
	}

}
