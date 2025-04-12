<?php

namespace Hytmng\PhpScff\Config;

use Hytmng\PhpScff\FileSystem\Path;

class PathResolver
{
	private Path $configDir;

	public function __construct(string $configDir)
	{
		$this->configDir = new Path($configDir);
	}

	public static function from(string $dir, string $name): self
	{
		return new self($dir . DIRECTORY_SEPARATOR . $name);
	}

	public function getConfigDir(): string
	{
		return $this->configDir->get();
	}

	public function getTemplateDir(): string
	{
		return $this->configDir->join('templates');
	}

	public function getGroupDir(): string
	{
		return $this->configDir->join('groups');
	}

	public function getDirsInConfigDir(): array
	{
		return [
			$this->getTemplateDir(),
			$this->getGroupDir(),
		];
	}
}
