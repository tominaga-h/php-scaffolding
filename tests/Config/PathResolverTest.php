<?php

namespace Tests\Config;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\Config\PathResolver;

class PathResolverTest extends TestCase
{
	private PathResolver $path;

	public function setUp(): void
	{
		$this->path = new PathResolver('path/to/config');
	}

	public function testFrom()
	{
		$path = PathResolver::from('path/to/config', 'phpscff');
		$this->assertEquals('path/to/config/phpscff', $path->getConfigDir());
	}

	public function testConfigDir()
	{
		$this->assertEquals('path/to/config', $this->path->getConfigDir());
	}

	public function testTemplateDir()
	{
		$this->assertEquals('path/to/config/templates', $this->path->getTemplateDir());
	}

	public function testGroupDir()
	{
		$this->assertEquals('path/to/config/groups', $this->path->getGroupDir());
	}

	public function testDirsInConfigDir()
	{
		$this->assertSame([
			'path/to/config/templates',
			'path/to/config/groups',
		], $this->path->getDirsInConfigDir());
	}
}
