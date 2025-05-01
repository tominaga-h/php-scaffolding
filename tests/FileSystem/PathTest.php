<?php

namespace Tests\FileSystem;

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
		$actual = $this->path->join('dir', 'file.txt')->get();
		$expected = '/test/dir/file.txt';
		$this->assertEquals($expected, $actual);
	}

	public function testJoin_sepalator()
	{
		$actual = $this->path->join('/dir/', '/file.txt')->get();
		$expected = '/test/dir/file.txt';
		$this->assertEquals($expected, $actual);
	}

	public function testBasename()
	{
		$actual = $this->path->join('dir', 'file.txt')->basename();
		$expected = 'file.txt';
		$this->assertEquals($expected, $actual);
	}

	public function testDirname()
	{
		$actual = $this->path->join('dir', 'file.txt')->dirname();
		$expected = '/test/dir';
		$this->assertEquals($expected, $actual);
	}

	public function testReplace()
	{
		$actual = $this->path->join('dir', 'file.txt')->replace('newfile.txt')->get();
		$expected = '/test/dir/newfile.txt';
		$this->assertEquals($expected, $actual);
	}
}
