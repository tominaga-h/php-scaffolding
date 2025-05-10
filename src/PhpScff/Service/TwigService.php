<?php

namespace Hytmng\PhpScff\Service;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigService
{
	private Environment $twig;

	private const DEFAULT_TEMPLATE_DIR = __DIR__ . '/../Resource/template';

	public function __construct()
	{
		$this->setTemplateDir(self::DEFAULT_TEMPLATE_DIR);
	}

	/**
	 * テンプレートの配置フォルダを設定する
	 */
	public function setTemplateDir(string $dir): void
	{
		$loader = new FilesystemLoader($dir);
		$this->twig = new Environment($loader);
	}

	/**
	 * テンプレートをレンダリングする
	 *
	 * @param string $template テンプレート名
	 * @param array $data テンプレートに渡すデータ
	 * @return string レンダリングされたテンプレートの内容
	 */
	public function render(string $template, array $data): string
	{
		return $this->twig->render($template, $data);
	}

	/**
	 * `meta.yaml.twig` をレンダリングする
	 *
	 * @param string $groupName グループ名
	 */
	public static function renderMetaYaml(string $groupName): string
	{
		$twig = new self();
		return $twig->render('meta.yaml.twig', [
			'groupName' => $groupName,
		]);
	}
}
