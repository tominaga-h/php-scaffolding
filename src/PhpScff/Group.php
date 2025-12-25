<?php

namespace Hytmng\PhpScff;

use Hytmng\PhpScff\Template;
use Hytmng\PhpScff\Helper\Msg;
use Hytmng\PhpScff\Helper\Filter;
use Hytmng\PhpScff\Service\TwigService;
use Hytmng\PhpScff\Process\EditProcess;
use Hytmng\PhpScff\FileSystem\PathTrait;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\Exception\ExistenceException;

class Group
{
	use PathTrait;

	private Directory $directory;
	private ?EditProcess $editProcess = null;

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
		$this->path = $this->directory->getPath();
		$this->directory = Directory::fromPath($this->path);
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
			$this->putMetaYaml();
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
		$filtered = array_filter($filtered, function (File $file) {
			return $file->getFilename() !== 'meta.yaml';
		});

		return array_values(array_map(function (File $item) {
			return Template::fromFile($item);
		}, $filtered));
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
	 * `meta.yaml` のパスを取得する
	 *
	 * @return Path
	 */
	public function getMetaYamlPath(): Path
	{
		return $this->path->join('meta.yaml');
	}

	/**
	 * グループディレクトリ配下に`meta.yaml` を作成する
	 */
	protected function putMetaYaml(): void
	{
		$path = $this->getMetaYamlPath();
		$file = File::fromPath($path);
		$content = TwigService::renderMetaYaml($this->getGroupName());
		$file->write($content);
	}

	/**
	 * `meta.yaml` を編集する
	 *
	 * @return bool 編集が成功した場合は true, 失敗した場合は false
	 */
	public function editMetaYaml(): bool
	{
		$path = $this->getMetaYamlPath();
		$file = File::fromPath($path);

		// meta.yamlの内容を読み取り、テンプレート一覧を挿入
		$content = $file->read();
		$content = $this->insertTemplateList($content);
		$file->write($content, true);

		$editor = $this->editProcess ?? new EditProcess();
		return $editor->edit($path->get());
	}

	/**
	 * meta.yamlの内容にテンプレート一覧を挿入する
	 *
	 * @param string $content meta.yamlの内容
	 * @return string テンプレート一覧が挿入されたmeta.yamlの内容
	 */
	private function insertTemplateList(string $content): string
	{
		$templates = $this->getTemplates();
		$templateList = $this->formatTemplateList($templates);

		// 既存のテンプレート一覧コメントのパターン
		// "(なし)" または "#   - filename" の行のみをマッチ
		$pattern = '/# 登録済みテンプレート:(?:\s*\(なし\)|(?:\n#   - [^\n]+)*)/u';

		if (preg_match($pattern, $content)) {
			// 既存のテンプレート一覧を更新
			return preg_replace($pattern, $templateList, $content);
		}

		// 旧形式のヒントコメントを置換
		$oldHint = '# グループに所属するテンプレートは `group:list -f` コマンドで確認できます。';
		if (str_contains($content, $oldHint)) {
			return str_replace($oldHint, $templateList, $content);
		}

		return $content;
	}

	/**
	 * テンプレート一覧をYAMLコメント形式でフォーマットする
	 *
	 * @param Template[] $templates テンプレートの配列
	 * @return string フォーマットされたテンプレート一覧
	 */
	private function formatTemplateList(array $templates): string
	{
		if (empty($templates)) {
			return '# 登録済みテンプレート: (なし)';
		}

		$lines = ['# 登録済みテンプレート:'];
		foreach ($templates as $template) {
			$lines[] = '#   - ' . $template->getFilename();
		}

		return implode("\n", $lines);
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

}
