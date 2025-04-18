<?php

namespace Hytmng\PhpScff\Process;

use Symfony\Component\Process\Process;
use Hytmng\PhpScff\Exception\ProcessException;

class EditProcess
{
	private string $editor;

	public function __construct()
	{
		$this->editor = $_SERVER['EDITOR'] ?? 'vim';
		$this->checkEditor();
	}

	/**
	 * エディタが存在するかどうかを確認する
	 *
	 * @return void
	 * @throws ProcessException エディタが存在しない場合
	 */
	public function checkEditor(): void
	{
		if (!$this->editorExists()) {
			$msg = 'Editor "' . $this->editor . '" is not found.' . PHP_EOL;
			$msg .= 'Please set your editor to the `EDITOR` environment variable.';
			throw new ProcessException($msg);
		}
	}

	/**
	 * Processオブジェクトを作成する
	 *
	 * @param array $command コマンドとその引数の配列
	 * @return Process
	 */
	public function createProcess(array $command): Process
	{
		return new Process($command);
	}

	/**
	 * エディタが存在するかどうかを確認する
	 *
	 * @return bool エディタが存在する場合は true, 存在しない場合は false
	 */
	public function editorExists(): bool
	{
		$proc = $this->createProcess(['which', $this->editor]);
		$proc->run();
		return $proc->isSuccessful();
	}

	/**
	 * ファイルを編集する
	 *
	 * @param string $path 編集するファイルのパス
	 * @return bool 編集が成功した場合は true, 失敗した場合は false
	 */
	public function edit(string $path): bool
	{
		$proc = $this->createProcess([$this->editor, $path]);
		$proc->setTty(true);
		$proc->run();
		return $proc->isSuccessful();
	}
}
