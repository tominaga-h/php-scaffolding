<?php

namespace Tests\PhpScff;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\Template;
use Hytmng\PhpScff\Process\EditProcess;
use Symfony\Component\Filesystem\Filesystem;
use PHPUnit\Framework\MockObject\MockObject;

class TemplateTest extends TestCase
{
	private string $testDir;
	private File $file;
	private Path $path;
	private Template $template;
	private Filesystem $filesystem;
	private EditProcess&MockObject $editProcess;

	public function setUp(): void
	{
		$this->filesystem = new Filesystem();
		$this->testDir = sys_get_temp_dir() . '/phpscff_test_' . uniqid();
		$this->filesystem->mkdir($this->testDir);

		// ファイルオブジェクト作成
		$this->path = Path::from($this->testDir, 'template.txt');
		$this->file = new File($this->path, $this->filesystem);

		// EditProcessのモックを作成
		$this->editProcess = $this->getMockBuilder(EditProcess::class)
			->disableOriginalConstructor()
			->getMock();

		// Templateオブジェクト作成
		$this->template = new Template($this->file, $this->filesystem);
		$this->template->setEditProcess($this->editProcess);
	}

	public function tearDown(): void
	{
		if ($this->filesystem->exists($this->testDir)) {
			$this->filesystem->remove($this->testDir);
		}
	}

	public function testGetPath()
	{
		$actual = $this->template->getStringPath();
		$expected = $this->path->get();
		$this->assertEquals($expected, $actual);
	}

	public function testGetFilename()
	{
		$actual = $this->template->getFilename();
		$expected = 'template.txt';
		$this->assertEquals($expected, $actual);
	}

	public function testFromPath()
	{
		$template = Template::fromPath($this->path);
		$actual = $template->getFilename();
		$expected = 'template.txt';
		$this->assertEquals($expected, $actual);
	}

	public function testFromStringPath()
	{
		$template = Template::fromStringPath($this->path->get());
		$actual = $template->getFilename();
		$expected = 'template.txt';
		$this->assertEquals($expected, $actual);
	}

	public function testCopy()
	{
		// テスト用のファイルを作成
		$this->file->write('test content');

		// コピー先のディレクトリを作成
		$copyDir = $this->testDir . '/copy';
		$this->filesystem->mkdir($copyDir);

		// コピー実行
		$this->template->copy($copyDir);

		// コピー結果の検証
		$expectedPath = $copyDir . '/template.txt';
		$this->assertTrue($this->filesystem->exists($expectedPath));
		$this->assertEquals('test content', file_get_contents($expectedPath));
	}

	public function testEdit()
	{
		// テスト用のファイルを作成
		$this->file->write('test content');

		// EditProcessのモックの振る舞いを設定
		$this->editProcess->expects($this->once())
			->method('edit')
			->with($this->path->get())
			->willReturn(true);

		// 編集メソッドを実行
		$result = $this->template->edit();

		// 編集結果の検証
		$this->assertTrue($result);
	}
}
