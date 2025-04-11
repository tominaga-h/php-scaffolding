<?php

namespace Hytmng\PhpScff\Config;

use Symfony\Component\Filesystem\Path;

class PathResolver
{
	private string $configDir;

	public function __construct(string $configDir)
	{
		$this->configDir = $configDir;
	}

	public static function from(string $dir, string $name): self
	{
		return new self(Path::join($dir, $name));
	}

	public function getConfigDir(): string
	{
		return $this->configDir;
	}

	public function getTemplateDir(): string
	{
		return Path::join($this->configDir, 'templates');
	}

	public function getGroupDir(): string
	{
		return Path::join($this->configDir, 'groups');
	}

	public function getDirsInConfigDir(): array
	{
		return [
			$this->getTemplateDir(),
			$this->getGroupDir(),
		];
	}
}
