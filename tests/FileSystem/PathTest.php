<?php

namespace Tests\Config;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\FileSystem\Path;

class PathTest extends TestCase
{
	private Path $path;

	public function setUp(): void
	{
		$this->path = new Path('/test');
	}

	public function testGet()
	{
		$actual = $this->path->get();
		$expected = '/test';
		$this->assertEquals($expected, $actual);
	}

	public function testJoin()
	{
		$actual = $this->path->join('dir', 'file.txt');
		$expected = '/test/dir/file.txt';
		$this->assertEquals($expected, $actual);
	}

	public function testJoin_sepalator()
	{
		$actual = $this->path->join('/dir/', '/file.txt');
		$expected = '/test/dir/file.txt';
		$this->assertEquals($expected, $actual);
	}
}
