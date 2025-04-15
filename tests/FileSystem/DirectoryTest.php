<?php

namespace Tests\FileSystem;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Hytmng\PhpScff\Exception\ExistenceException;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\FileSystem\Directory;

class DirectoryTest extends TestCase
{
	private Directory $directory;
	private Path $path;
	private vfsStreamDirectory $root;

	public function setUp(): void
	{
		// テスト環境構築
		$this->root = vfsStream::setup('test');

		// ディレクトリオブジェクト作成
		$this->path = Path::from($this->root->url());
		$this->directory = new Directory($this->path, new Filesystem());
	}

	public function tearDown(): void
	{
		// テスト環境の削除
		$children = $this->root->getChildren();
		foreach ($children as $child) {
			$name = $child->getName();
			if ($this->root->hasChild($name)) {
				$this->root->removeChild($name);
			}
		}
	}

	public function testFromPath()
	{
		$expected = '/test/path/directory';
		$directory = Directory::fromStringPath($expected);
		$actual = $directory->getStringPath();
		$this->assertEquals($expected, $actual);
	}

	public function testGetDirPath()
	{
		$actual = $this->directory->getStringPath();
		$expected = $this->root->url();
		$this->assertEquals($expected, $actual);
	}

	public function testExists()
	{
		$path = Path::from($this->root->url(), 'testdir');
		$this->directory = Directory::fromPath($path);
		$this->assertFalse($this->directory->exists());

		vfsStream::newDirectory('testdir')->at($this->root);
		$this->assertTrue($this->directory->exists());
	}

	public function testCreate()
	{
		$path = Path::from($this->root->url(), 'testdir');
		$this->directory = Directory::fromPath($path);
		$this->assertFalse($this->directory->exists());

		$this->directory->create();
		$this->assertTrue($this->directory->exists());

		$this->expectException(ExistenceException::class);
		$this->expectExceptionMessage('Directory "' . $path->get() . '" is already exists');
		$this->directory->create();
	}

	public function testRemove()
	{
		$path = Path::from($this->root->url(), 'testdir');
		$this->directory = Directory::fromPath($path);
		vfsStream::newDirectory('testdir')->at($this->root);
		$this->assertTrue($this->directory->exists());

		$this->directory->remove();
		$this->assertFalse($this->directory->exists());

		$this->expectException(ExistenceException::class);
		$this->expectExceptionMessage('Directory "' . $path->get() . '" is not exists');
		$this->directory->remove();
	}

	public function testList()
	{
		vfsStream::newFile('file1.txt')->at($this->root);
		$subdir1 = vfsStream::newDirectory('subdir1')->at($this->root);
		vfsStream::newFile('file3.txt')->at($subdir1);

		$list = $this->directory->list();

		$this->assertEquals(count($list), 2);

		$this->assertTrue($list[0]->isFile());
		$this->assertEquals($list[0]->getStringPath(), $this->root->url() . '/file1.txt');

		$this->assertTrue($list[1]->isDir());
		$this->assertEquals($list[1]->getStringPath(), $this->root->url() . '/subdir1');
	}

	public function testList_Recursive()
	{
		vfsStream::newFile('file1.txt')->at($this->root);
		$subdir1 = vfsStream::newDirectory('subdir1')->at($this->root);
		vfsStream::newFile('file3.txt')->at($subdir1);

		$list = $this->directory->list(true);

		$this->assertEquals(count($list), 2);

		$this->assertTrue($list[0]->isFile());
		$this->assertEquals($list[0]->getStringPath(), $this->root->url() . '/file1.txt');

		$this->assertTrue($list[1]->isFile());
		$this->assertEquals($list[1]->getStringPath(), $this->root->url() . '/subdir1/file3.txt');
	}

	public function testList_throwException()
	{
		$path = Path::from($this->root->url(), 'notfound');
		$this->directory = Directory::fromPath($path);
		$this->expectException(ExistenceException::class);
		$this->expectExceptionMessage('Directory "' . $path->get() . '" is not exists');
		$this->directory->list();
	}

	public function testGetIterator()
	{
		vfsStream::newFile('file1.txt')->at($this->root);
		$subdir1 = vfsStream::newDirectory('subdir1')->at($this->root);
		vfsStream::newFile('file3.txt')->at($subdir1);

		$iter = $this->directory->getIterator();
		$count = 0;
		foreach ($iter as $item) {
			if ($count === 0) {
				$this->assertTrue($item->isFile());
				$this->assertEquals($item->getStringPath(), $this->root->url() . '/file1.txt');
			} else {
				$this->assertTrue($item->isDir());
				$this->assertEquals($item->getStringPath(), $this->root->url() . '/subdir1');
			}
			$count++;
		}
	}
}
