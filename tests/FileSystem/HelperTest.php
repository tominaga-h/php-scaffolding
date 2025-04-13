<?php

namespace Tests\PhpScff\FileSystem;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use Hytmng\PhpScff\FileSystem\Helper;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\File;


class HelperTest extends TestCase
{
	private vfsStreamDirectory $root;
	private vfsStreamDirectory $dir;
	private vfsStreamFile $file;

	public function setUp(): void
	{
		$this->root = vfsStream::setup('test');
		$this->dir = vfsStream::newDirectory('test')->at($this->root);
		$this->file = vfsStream::newFile('file.txt')->at($this->root);
	}

	public function testConvertObject_dir()
	{
		$obj = Helper::convertObject($this->dir->url());
		$this->assertInstanceOf(Directory::class, $obj);
	}

	public function testConvertObject_file()
	{
		$obj = Helper::convertObject($this->file->url());
		$this->assertInstanceOf(File::class, $obj);
	}

	public function testConvertObject_throwException()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid path: path/to/wherever');
		Helper::convertObject('path/to/wherever');
	}
}
