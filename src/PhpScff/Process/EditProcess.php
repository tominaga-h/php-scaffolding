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
		if (!$this->editorExists()) {
			$msg = 'Editor "' . $this->editor . '" is not found.' . PHP_EOL;
			$msg .= 'Please set your editor to the `EDITOR` environment variable.';
			throw new ProcessException($msg);
		}
	}

	private function editorExists(): bool
	{
		$proc = new Process(['which', $this->editor]);
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
		$proc = new Process([$this->editor, $path]);
		$proc->setTty(true);
		$proc->run();
		return $proc->isSuccessful();
	}
}
