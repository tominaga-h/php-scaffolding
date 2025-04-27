<?php

namespace Hytmng\PhpScff\Helper;

use Symfony\Component\Filesystem\Path as SymfonyPath;

class Msg
{
	public const FLG_NOT_FOUND = 0;
	public const FLG_ADDED = 1;
	public const FLG_ALREADY_EXISTS = 2;

	public const SPACE = ' ';
	public const QUOTE = '"';
	public const PERIOD = '.';

	/**
	 * テンプレートメッセージを作成する
	 *
	 * @param string $template テンプレート
	 * @param string|null $group グループ名
	 * @param int $flg メッセージフラグ
	 * @param string|null $templateColor テンプレートの色
	 * @param array $templateOptions テンプレートのオプション
	 * @param string|null $groupColor グループの色
	 * @param array $groupOptions グループのオプション
	 * @return string
	 */
	public static function makeTemplateMsg(
		int $flg,
		string $template,
		?string $group = null,
		?string $templateColor = null,
		array $templateOptions = [],
		?string $groupColor = null,
		array $groupOptions = []
	): string
	{
		$msg = 'Template' . self::SPACE;
		$msg .= self::quote(self::style($template, $templateColor, $templateOptions)) . self::SPACE;
		$msg .= self::getFlgMsg($flg);

		if (!\is_null($group)) {
			$msg .= self::SPACE . 'in group' . self::SPACE;
			$msg .= self::quote(self::style($group, $groupColor, $groupOptions));
		}

		$msg .= self::PERIOD;

		return $msg;
	}

	public static function quote(string $msg): string
	{
		return self::QUOTE . $msg . self::QUOTE;
	}

	public static function getFlgMsg(int $flg): string
	{
		switch ($flg) {
			case self::FLG_NOT_FOUND:
				return 'not found';
			case self::FLG_ADDED:
				return 'added';
			case self::FLG_ALREADY_EXISTS:
				return 'already exists';
			default:
				return '';
		}
	}

	public static function style(string $msg, ?string $color = null, array $options = []): string
	{
		// 色の指定がなければスタイリングしない
		if (\is_null($color)) {
			return $msg;
		}

		$style = '<fg=' . $color;

		$optionCount = \count($options);
		if ($optionCount > 1) {
			$style .= ';options=' . \implode(',', $options);
		} else if ($optionCount === 1) {
			$style .= ';options=' . $options[0];
		}

		$style .= '>';
		$style .= $msg;
		$style .= '</>';

		return $style;
	}
}
