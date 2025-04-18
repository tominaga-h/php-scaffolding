<?php

namespace Tests\FileSystem;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\Exception\ExistenceException;

class FileTest extends TestCase
{
	private File $file;
	private Path $path;
	private vfsStreamDirectory $root;

	public function setUp(): void
	{
		// テスト環境構築
		$this->root = vfsStream::setup('test');

		// ファイルオブジェクト作成
		$this->path = Path::from($this->root->url(), 'file.txt');
		$this->file = new File($this->path, new Filesystem());
	}

	public function tearDown(): void
	{
		// テスト環境の削除
		$this->root->removeChild($this->path->basename());
	}

	public function testFromPath()
	{
		$expected = '/test/path/file.txt';
		$file = File::fromStringPath($expected);
		$actual = $file->getStringPath();
		$this->assertEquals($expected, $actual);
	}

	public function testGetFilePath()
	{
		$actual = $this->file->getStringPath();
		$expected = $this->root->url() . '/file.txt';
		$this->assertEquals($expected, $actual);
	}

	public function testGetFilename()
	{
		$actual = $this->file->getFilename();
		$expected = 'file.txt';
		$this->assertEquals($expected, $actual);
	}

	public function testExists()
	{
		$this->assertFalse($this->file->exists());

		// テスト用のファイルを作成
		$testFile = vfsStream::newFile('file.txt');
		$testFile->withContent('test content');
		$this->root->addChild($testFile);

		$this->assertTrue($this->file->exists());
	}

	public function testRead()
	{
		// テスト用のファイルを作成
		$testFile = vfsStream::newFile('file.txt');
		$testFile->withContent('test content');
		$this->root->addChild($testFile);

		$actual = $this->file->read();
		$expected = 'test content';
		$this->assertEquals($expected, $actual);
	}

	public function testWrite()
	{
		$this->assertFalse($this->file->exists());

		$expected = 'test content';
		$this->file->write($expected);
		$this->assertTrue($this->file->exists());

		$actual = $this->file->read();
		$this->assertEquals($expected, $actual);
	}

	public function testWrite_Overwrite()
	{
		$expected = 'test content';

		$this->assertFalse($this->file->exists());

		// step1. create file
		$this->file->write($expected);
		$this->assertTrue($this->file->exists());

		// step2. overwrite file
		$expected .= ' overwritten';
		$this->file->write($expected, true);
		$actual = $this->file->read();
		$this->assertEquals($expected, $actual);
	}

	public function testWrite_throwException()
	{
		$expected = 'test content';

		$this->assertFalse($this->file->exists());

		$this->file->write($expected);
		$this->assertTrue($this->file->exists());

		$this->expectException(ExistenceException::class);
		$this->file->write($expected);
	}
}
