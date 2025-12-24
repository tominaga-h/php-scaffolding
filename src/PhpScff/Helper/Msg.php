<?php

namespace Hytmng\PhpScff\Helper;

/**
 * メッセージ作成に関するメソッドを集約したヘルパークラス
 */
class Msg
{
	public const FLG_NOT_FOUND = 0;
	public const FLG_ADDED = 1;
	public const FLG_ALREADY_EXISTS = 2;

	public const LOG_CREATED = 1;
	public const LOG_DELETED = 2;
	public const LOG_FAILED = 3;
	public const LOG_ERROR = 4;

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

	/**
	 * ログのプレフィックスを作成する
	 *
	 * @param int $logType ログの種類
	 * @param string $msg ログのメッセージ
	 * @return string
	 */
	public static function makeLogPrefix(int $logType, string $msg)
	{
		/** @var string|null $color */
		$color = null;
		/** @var string|null $prefix */
		$prefix = null;
		/** @var int $margin */
		$margin = 0;

		switch ($logType) {
			case self::LOG_CREATED:
				$color = 'blue';
				$prefix = 'CREATED';
				$margin = 0;
				break;
			case self::LOG_DELETED:
				$color = 'red';
				$prefix = 'DELETED';
				$margin = 0;
				break;
			case self::LOG_FAILED:
				$color = 'red';
				$prefix = 'FAILED';
				$margin = 1;
				break;
			case self::LOG_ERROR:
				$color = 'red';
				$prefix = 'ERROR';
				$margin = 2;
				break;
		}

		return '[' . self::style($prefix, $color, ['bold']) . str_repeat(self::SPACE, $margin) . '] ' . $msg;

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
