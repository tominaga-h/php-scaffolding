<?php

namespace Hytmng\PhpScff\FileSystem;

interface FileSystemInterface
{
	public static function fromPath(Path $path): self;

	public static function fromStringPath(string $path): self;

	public function getPath(): Path;

	public function getStringPath(): string;

	public function isDir(): bool;

	public function isFile(): bool;
}
