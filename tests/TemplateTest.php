<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\Template;
use RuntimeException;
use Hytmng\PhpScff\Exception\NotFoundException;
use Hytmng\PhpScff\Exception\PermissionException;

class TemplateTest extends TestCase
{
	private string $templatePath;
	private string $templateFileName;
	private Template $template;

	public function setUp(): void
	{
		// テンプレートファイルを作成
		$directory = \sys_get_temp_dir();
		$filename = 'tmplte_' . \uniqid();
		$this->templateFileName = $filename;
		$this->templatePath = $directory . DIRECTORY_SEPARATOR . $filename;
		$this->template = new Template($this->templatePath);
	}

	public function tearDown(): void
	{
		// テンプレートファイルの削除
		if (\file_exists($this->templatePath)) {
			\unlink($this->templatePath);
		}
	}

	public function testPath()
	{
		$actual = $this->template->getPath();
		$expected = $this->templatePath;
		$this->assertEquals($expected, $actual);
	}

	public function testFileName()
	{
		$actual = $this->template->getFileName();
		$expected = $this->templateFileName;
		$this->assertEquals($expected, $actual);
	}

	public function testExists()
	{
		$this->assertFalse($this->template->exists());

		$this->template->write("test\n");
		$this->assertTrue($this->template->exists());
	}

	public function testFilePerms()
	{
		$this->template = new Template('/bin/ls');
		$perms = $this->template->getFilePerms();
		$actual = substr(sprintf('%o', $perms), -4);
		$expected = '0755';
		$this->assertEquals($expected, $actual);
	}

	public function testReadble()
	{
		$this->assertFalse($this->template->isReadable());

		$this->template->write("test\n");
		$this->assertTrue($this->template->isReadable());
	}

	public function testWritable()
	{
		$this->assertFalse($this->template->isWritable());

		$this->template->write("test\n");
		$this->assertTrue($this->template->isWritable());
	}

	public function testGet()
	{
		$this->template->write("test\n");
		$actual = $this->template->get();
		$expected = "test\n";
		$this->assertEquals($expected, $actual);
	}

	public function testGet_NotExists()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('テンプレートが存在しません');

		$this->template->get();
	}

	public function testGet_NotReadable()
	{
		$template = $this->getMockBuilder(Template::class)
			->setConstructorArgs([$this->templatePath])
			->onlyMethods(['isReadable'])
			->getMock();
		$template->expects($this->once())
			->method('isReadable')
			->willReturn(false);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('テンプレートが読み込み可能ではありません');

		$template->write("test\n");
		$template->get();
	}

	public function testGet_FailedGetContents()
	{
		$template = $this->getMockBuilder(Template::class)
			->setConstructorArgs([$this->templatePath])
			->onlyMethods(['exists', 'isReadable'])
			->getMock();
		$template->expects($this->once())
			->method('exists')
			->willReturn(true);
		$template->expects($this->once())
			->method('isReadable')
			->willReturn(true);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('テンプレートの内容を取得できませんでした');

		$template->get();
	}

	public function testWrite()
	{
		$this->template->write("test\n");
		$actual = $this->template->get();
		$expected = "test\n";
		$this->assertEquals($expected, $actual);
	}

	public function testWrite_ThrowsException()
	{
		$template = $this->getMockBuilder(Template::class)
			->setConstructorArgs([$this->templatePath])
			->onlyMethods(['exists', 'isWritable'])
			->getMock();
		$template->expects($this->once())
			->method('exists')
			->willReturn(true);
		$template->expects($this->once())
			->method('isWritable')
			->willReturn(false);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('テンプレートが書き込み可能ではありません');

		$template->write("test\n");
	}

	public function testWrite_FailedPutContents()
	{
		$template = $this->getMockBuilder(Template::class)
			->setConstructorArgs(['/root'])
			->onlyMethods(['exists'])
			->getMock();
		$template->expects($this->once())
			->method('exists')
			->willReturn(false);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('テンプレートの内容を書き込めませんでした');

		$template->write("test\n");
	}

}
