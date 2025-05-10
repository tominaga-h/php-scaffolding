<?php

namespace Hytmng\PhpScff\Helper;

use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\FileSystemInterface;
use Hytmng\PhpScff\Template;

/**
 * `array_filter`を使ったフィルターメソッドを集約したヘルパークラス
 */
class Filter
{
	/**
	 * Fileインスタンスのみを含む配列を返す
	 *
	 * @param FileSystemInterface[] $files
	 * @return File[]
	 */
	public static function byFileInstance(array $files): array
	{
		return array_values(array_filter($files, function (FileSystemInterface $item) {
			return $item instanceof File;
		}));
	}

	/**
	 * 名前を指定したテンプレートフィルタリング
	 *
	 * @param Template[] $templates
	 * @param string $name テンプレート名（ファイル名）
	 * @return Template[]
	 */
	public static function byTemplateName(array $templates, string $name): array
	{
		return array_values(array_filter($templates, function (Template $template) use ($name) {
			return $template->getFilename() === $name;
		}));
	}
}
