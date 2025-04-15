<?php

namespace Hytmng\PhpScff\FileSystem;

interface FileSystemInterface
{
	public function getPath(): Path;

	public function getStringPath(): string;

	public function isDir(): bool;

	public function isFile(): bool;
}
