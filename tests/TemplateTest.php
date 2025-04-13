<?php

namespace Tests\PhpScff;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\Template;
use Symfony\Component\Filesystem\Filesystem;

class TemplateTest extends TestCase
{
	private File $file;
	private Path $path;
	private Template $template;
	private vfsStreamDirectory $root;

	public function setUp(): void
	{
		// テスト環境構築
		$this->root = vfsStream::setup('test');

		// ファイルオブジェクト作成
		$this->path = Path::from($this->root->url(), 'template.txt');
		$this->file = new File($this->path, new Filesystem());

		// Templateオブジェクト作成
		$this->template = new Template($this->file, new Filesystem());
	}

	public function testGetFilename()
	{
		$actual = $this->template->getFilename();
		$expected = 'template.txt';
		$this->assertEquals($expected, $actual);
	}

	public function testFromPath()
	{
		$template = Template::fromPath($this->path->get());
		$actual = $template->getFilename();
		$expected = 'template.txt';
		$this->assertEquals($expected, $actual);
	}

	public function testCopy()
	{
		// テスト用のファイルを作成
		$testFile = vfsStream::newFile('template.txt');
		$testFile->withContent('test content');
		$this->root->addChild($testFile);

		// コピー先のディレクトリを作成
		$copyDir = vfsStream::newDirectory('copy');
		$this->root->addChild($copyDir);

		// コピー実行
		$dest = Path::from($this->root->url(), 'copy', 'template.txt');
		$this->template->copy($dest->get());

		// コピー結果の検証
		$this->assertDirectoryExists($this->root->url() . '/copy');
		$this->assertFileExists($this->root->url() . '/copy/template.txt');
		$this->assertEquals('test content', file_get_contents($this->root->url() . '/copy/template.txt'));
	}
}
