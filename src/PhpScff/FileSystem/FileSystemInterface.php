<?php

namespace Hytmng\PhpScff\FileSystem;

interface FileSystemInterface
{
	public function getPath(): string;

	public function isDir(): bool;

	public function isFile(): bool;
}
