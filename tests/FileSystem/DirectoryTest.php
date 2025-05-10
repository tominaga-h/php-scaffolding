<?php

namespace Tests\FileSystem;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Hytmng\PhpScff\Exception\ExistenceException;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\File;

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

	public function testCreate_ThrowException()
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

	public function testRemove_ThrowException()
	{
		$path = Path::from($this->root->url(), 'testdir');
		$this->directory = Directory::fromPath($path);
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

	public function testList_ThrowException()
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

	public function testGetSubDirPath(): void
	{
		// 単一のサブディレクトリ
		$subDirPath = $this->directory->getSubDirPath('subdir1');
		$this->assertInstanceOf(Path::class, $subDirPath);
		$this->assertEquals($this->root->url() . '/subdir1', $subDirPath->get());

		// 複数階層のサブディレクトリ
		$subDirPath = $this->directory->getSubDirPath('subdir1', 'subdir2');
		$this->assertEquals($this->root->url() . '/subdir1/subdir2', $subDirPath->get());
	}

	public function testGetSubDir(): void
	{
		// 単一のサブディレクトリ
		$subDir = $this->directory->getSubDir('subdir1');
		$this->assertInstanceOf(Directory::class, $subDir);
		$this->assertEquals($this->root->url() . '/subdir1', $subDir->getStringPath());

		// 複数階層のサブディレクトリ
		$subDir = $this->directory->getSubDir('subdir1', 'subdir2');
		$this->assertEquals($this->root->url() . '/subdir1/subdir2', $subDir->getStringPath());

		// 存在確認（getSubDirは存在しないディレクトリのオブジェクトも返す）
		$this->assertFalse($subDir->exists());
		vfsStream::newDirectory('subdir1/subdir2', 0777)->at($this->root);
		$this->assertTrue($subDir->exists());
	}

	public function testGetFilePath(): void
	{
		$filePath = $this->directory->getFilePath('test.txt');
		$this->assertInstanceOf(Path::class, $filePath);
		$this->assertEquals($this->root->url() . '/test.txt', $filePath->get());
	}

	public function testGetFile(): void
	{
		// ファイルオブジェクトの取得
		$file = $this->directory->getFile('test.txt');
		$this->assertInstanceOf(File::class, $file);
		$this->assertEquals($this->root->url() . '/test.txt', $file->getStringPath());

		// 存在確認（getFileは存在しないファイルのオブジェクトも返す）
		$this->assertFalse($file->exists());
		vfsStream::newFile('test.txt')->at($this->root);
		$this->assertTrue($file->exists());
	}

	public function testRename(): void
	{
		// テスト用ディレクトリの作成
		$path = Path::from($this->root->url(), 'olddir');
		$this->directory = Directory::fromPath($path);
		vfsStream::newDirectory('olddir')->at($this->root);
		$this->assertTrue($this->directory->exists());

		// ディレクトリ名の変更
		$this->directory->rename('newdir');

		// 古いディレクトリが存在しないことを確認
		$this->assertFalse($this->directory->exists());

		// 新しいディレクトリが存在することを確認
		$newPath = Path::from($this->root->url(), 'newdir');
		$newDir = Directory::fromPath($newPath);
		$this->assertTrue($newDir->exists());
	}

	public function testRename_ThrowException(): void
	{
		// 存在しないディレクトリのパス
		$path = Path::from($this->root->url(), 'notfound');
		$this->directory = Directory::fromPath($path);

		$this->expectException(ExistenceException::class);
		$this->expectExceptionMessage('Directory "' . $path->get() . '" is not exists');
		$this->directory->rename('newname');
	}
}
