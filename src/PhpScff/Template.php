<?php

namespace Hytmng\PhpScff;

use RuntimeException;
use Hytmng\PhpScff\Exception\NotFoundException;
use Hytmng\PhpScff\Exception\PermissionException;

class Template
{
	private string $path;

	public function __construct(string $path)
	{
		$this->path = $path;
	}

	/**
	 * テンプレートのパスを返す
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * テンプレートのファイル名を返す
	 *
	 * @return string
	 */
	public function getFileName(): string
	{
		return \basename($this->path);
	}

	/**
	 * テンプレートが存在するかどうかを返す
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		return \file_exists($this->path);
	}

	/**
	 * テンプレートのファイルパーミッションを返す
	 *
	 * @return int
	 * @throws PermissionException パーミッションを取得できない場合
	 */
	public function getFilePerms(): int
	{
		$perms = \fileperms($this->path);

		if ($perms === false) {
			throw new PermissionException('パーミッションを取得できませんでした');
		}

		return $perms;
	}

	/**
	 * テンプレートが読み込み可能かどうかを返す
	 *
	 * @return bool
	 */
	public function isReadable(): bool
	{
		if (!$this->exists()) {
			return false;
		}

		$perms = $this->getFilePerms();
		$ownerReadable = ($perms & 0x0100) ? true : false;
		$groupReadable = ($perms & 0x0020) ? true : false;
		$othersReadable = ($perms & 0x0004) ? true : false;

		return $ownerReadable || $groupReadable || $othersReadable;
	}

	/**
	 * テンプレートが書き込み可能かどうかを返す
	 *
	 * @return bool
	 */
	public function isWritable(): bool
	{
		if (!$this->exists()) {
			return false;
		}

		$perms = $this->getFilePerms();
		$ownerWritable = ($perms & 0x0080) ? true : false;
		$groupWritable = ($perms & 0x0010) ? true : false;
		$othersWritable = ($perms & 0x0002) ? true : false;

		return $ownerWritable || $groupWritable || $othersWritable;
	}

	/**
	 * テンプレートの内容を取得する
	 *
	 * @return string
	 * @throws NotFoundException テンプレートが存在しない場合
	 * @throws PermissionException テンプレートが読み込み可能でない場合
	 * @throws RuntimeException テンプレートの内容を取得できない場合
	 */
	public function get(): string
	{
		if (!$this->exists()) {
			throw new NotFoundException('テンプレートが存在しません');
		}

		if (!$this->isReadable()) {
			throw new PermissionException('テンプレートが読み込み可能ではありません');
		}

		$content = @\file_get_contents($this->path);

		if ($content === false) {
			throw new RuntimeException('テンプレートの内容を取得できませんでした');
		}

		return $content;
	}

	/**
	 * テンプレートの内容を書き込む
	 *
	 * @param string $content
	 * @throws PermissionException テンプレートが書き込み可能でない場合
	 * @throws RuntimeException テンプレートの内容を書き込めない場合
	 */
	public function write(string $content): void
	{
		// 存在しない場合は権限を確認しない
		if ($this->exists()) {
			if (!$this->isWritable()) {
				throw new PermissionException('テンプレートが書き込み可能ではありません');
			}
		}

		$result = @\file_put_contents($this->path, $content);

		if ($result === false) {
			throw new RuntimeException('テンプレートの内容を書き込めませんでした');
		}
	}
}
