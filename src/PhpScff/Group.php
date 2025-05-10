<?php

namespace Hytmng\PhpScff;

use Hytmng\PhpScff\FileSystem\PathTrait;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\Exception\ExistenceException;
use Hytmng\PhpScff\Template;
use Hytmng\PhpScff\Helper\Msg;
use Hytmng\PhpScff\Helper\Filter;
use Hytmng\PhpScff\Service\TwigService;

class Group
{
	use PathTrait;

	private Directory $directory;

	public function __construct(Directory $directory)
	{
		$this->directory = $directory;
		$this->path = $directory->getPath();
	}

	/**
	 * グループ名を取得する
	 *
	 * @return string
	 */
	public function getGroupName(): string
	{
		return $this->directory->getDirName();
	}

	/**
	 * グループディレクトリが存在するかどうかを確認する
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		return $this->directory->exists();
	}

	/**
	 * グループディレクトリを作成する
	 * 同時に `meta.yaml` を配下に作成する
	 *
	 * @throws ExistenceException
	 */
	public function create(): void
	{
		$this->directory->create();
		$this->putMetaYaml();
	}

	/**
	 * グループディレクトリを削除する
	 *
	 * @throws ExistenceException
	 */
	public function remove(): void
	{
		$this->directory->remove();
	}

	/**
	 * グループ名を変更する
	 *
	 * @param string $newname
	 * @throws ExistenceException
	 */
	public function rename(string $newname): void
	{
		$this->directory->rename($newname);
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
			throw new ExistenceException('Template ' . Msg::quote($filename) . ' is already exists.');
		}

		if (!$this->directory->exists()) {
			$this->directory->create();
		}

		$template->copy($this->directory->getStringPath());
	}

	/**
	 * 全てのテンプレートを取得する
	 *
	 * @return Template[]
	 * @throws ExistenceException
	 */
	public function getTemplates(): array
	{
		if (!$this->directory->exists()) {
			throw new ExistenceException('Group ' . Msg::quote($this->getGroupName()) . ' is not exists');
		}

		$files = $this->directory->list(false);
		$filtered = Filter::byFileInstance($files);

		return array_map(function (File $item) {
			return Template::fromFile($item);
		}, $filtered);
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
		$templates = $this->getTemplates();
		$filtered = Filter::byTemplateName($templates, $name);
        if (\count($filtered) > 0) {
            return \array_values($filtered)[0];
        }
        throw new ExistenceException('Template ' . Msg::quote($name) . ' is not exists.');
	}

	/**
	 * テンプレートが存在するかどうかを確認する
	 *
	 * @param string|Template $template
	 * @return bool
	 */
	public function hasTemplate(string|Template $template): bool
	{
		if (!$this->directory->exists()) {
			return false;
		}

		if ($template instanceof Template) {
			$filename = $template->getFilename();
		} else {
			$filename = $template;
		}

		return $this->directory->getFile($filename)->exists();
	}

	/**
	 * グループディレクトリ配下に`meta.yaml` を作成する
	 */
	protected function putMetaYaml(): void
	{
		$path = $this->path->join('meta.yaml');
		$file = File::fromPath($path);
		$content = TwigService::renderMetaYaml($this->getGroupName());
		$file->write($content);
	}

}
