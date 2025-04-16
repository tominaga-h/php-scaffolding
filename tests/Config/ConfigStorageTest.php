<?php

namespace Tests\Config;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\Config\ConfigStorage;
use Hytmng\PhpScff\Config\PathResolver;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\Template;
use Hytmng\PhpScff\Exception\ExistenceException;
use Symfony\Component\Filesystem\Filesystem;

class ConfigStorageTest extends TestCase
{
	private string $testDir;
	private ConfigStorage $configStorage;
	private Filesystem $filesystem;

	public function setUp(): void
	{
		$this->filesystem = new Filesystem();
		$this->testDir = Path::from(sys_get_temp_dir(), '/phpscff_test_' . uniqid())->get();
		$this->configStorage = new ConfigStorage($this->testDir, '.phpscff');
		$this->configStorage->setPathResolver(new PathResolver($this->testDir));
	}

	public function tearDown(): void
	{
		if ($this->filesystem->exists($this->testDir)) {
			$this->filesystem->remove($this->testDir);
		}
	}

	public function testConfigDir()
	{
		$configDir = $this->configStorage->getConfigDir();
		$this->assertInstanceOf(Directory::class, $configDir);

		$actual = $configDir->getStringPath();
		$expected = $this->testDir;
		$this->assertEquals($expected, $actual);
	}

	public function testTemplateDir()
	{
		$templateDir = $this->configStorage->getTemplateDir();
		$this->assertInstanceOf(Directory::class, $templateDir);

		$actual = $templateDir->getStringPath();
		$expected = $this->testDir . '/templates';
		$this->assertEquals($expected, $actual);
	}

	public function testGroupDir()
	{
		$groupDir = $this->configStorage->getGroupDir();
		$this->assertInstanceOf(Directory::class, $groupDir);

		$actual = $groupDir->getStringPath();
		$expected = $this->testDir . '/groups';
		$this->assertEquals($expected, $actual);
	}

	public function testExists()
	{
		$this->assertFalse($this->configStorage->exists());

		$this->configStorage->create();
		$this->assertTrue($this->configStorage->exists());
	}

	public function testCreate()
	{
		$this->configStorage->create();
		$this->assertTrue($this->configStorage->exists());

		$configDir = $this->configStorage->getConfigDir();
		$this->assertTrue($configDir->exists());

		$templateDir = $this->configStorage->getTemplateDir();
		$this->assertTrue($templateDir->exists());

		$groupDir = $this->configStorage->getGroupDir();
		$this->assertTrue($groupDir->exists());
	}

	public function testRemove()
	{
		$this->configStorage->create();
		$this->assertTrue($this->configStorage->exists());

		$configDir = $this->configStorage->getConfigDir();
		$this->assertTrue($configDir->exists());

		$templateDir = $this->configStorage->getTemplateDir();
		$this->assertTrue($templateDir->exists());

		$groupDir = $this->configStorage->getGroupDir();
		$this->assertTrue($groupDir->exists());

		$this->configStorage->remove();
		$this->assertFalse($this->configStorage->exists());

		$this->assertFalse($configDir->exists());
		$this->assertFalse($templateDir->exists());
		$this->assertFalse($groupDir->exists());

		$this->expectException(ExistenceException::class);
		$this->expectExceptionMessage('Config directory is not exists');
		$this->configStorage->remove();
	}

	public function testCreate_throwException()
	{
		$this->configStorage->create();
		$this->expectException(ExistenceException::class);
		$this->expectExceptionMessage('Config directory is already exists');

		$this->configStorage->create();
	}

	public function testAddTemplate()
	{
		// 設定ディレクトリを作成
		$this->configStorage->create();

		// テンプレートファイルを作成
		$templatePath = $this->testDir . '/template.txt';
		$file = File::fromStringPath($templatePath);
		$file->write('test content');

		// テンプレートオブジェクトを作成
		$template = new Template($file, new Filesystem());

		// テンプレートを追加
		$this->configStorage->addTemplate($template);

		// テンプレートがコピーされているか確認
		$templateDir = $this->configStorage->getTemplateDir();
		$this->assertTrue($templateDir->exists());

		$expectedPath = $templateDir->getStringPath() . '/template.txt';
		$this->assertTrue($this->filesystem->exists($expectedPath));

		// コピーされたファイルの内容を確認
		$this->assertEquals('test content', file_get_contents($expectedPath));

		$this->expectException(ExistenceException::class);
		$this->expectExceptionMessage('Template "template.txt" is already exists.');
		$this->configStorage->addTemplate($template);
	}

	public function testAddTemplate_DirectoryNotExists()
	{
		// テンプレートファイルを作成
		$templatePath = $this->testDir . '/template.txt';
		$file = File::fromStringPath($templatePath);
		$file->write('test content');

		// テンプレートオブジェクトを作成
		$template = new Template($file, new Filesystem());

		// テンプレートを追加
		$this->expectException(ExistenceException::class);
		$this->configStorage->addTemplate($template);

		$templateDir = $this->configStorage->getTemplateDir();
		$this->assertFalse($templateDir->exists());
	}

	public function testGetTemplates()
	{
		$this->configStorage->create();

		$templatePath = Path::from($this->testDir, '/templates/template.txt');
		$file = File::fromPath($templatePath);
		$file->write('test content');

		$templates = $this->configStorage->getTemplates();
		$this->assertCount(1, $templates);
		$this->assertInstanceOf(Template::class, $templates[0]);
		$this->assertEquals('template.txt', $templates[0]->getFilename());
	}

	public function testGetTemplate()
	{
		$this->configStorage->create();

		$templatePath = Path::from($this->testDir, '/templates/template.txt');
		$file = File::fromPath($templatePath);
		$file->write('test content');

		$template = $this->configStorage->getTemplate('template.txt');
		$this->assertInstanceOf(Template::class, $template);
		$this->assertEquals('template.txt', $template->getFilename());
	}

	public function testGetTemplate_throwException()
	{
		$this->configStorage->create();

		$this->expectException(ExistenceException::class);
		$this->expectExceptionMessage('Template "not_exists.txt" is not exists.');
		$this->configStorage->getTemplate('not_exists.txt');
	}

	public function testHasTemplate()
	{
		$this->configStorage->create();

		// テンプレートファイルを作成
		$templatePath = Path::from($this->testDir, '/templates/template.txt');
		$file = File::fromPath($templatePath);
		$file->write('test content');

		// テンプレートの存在確認
		$this->assertTrue($this->configStorage->hasTemplate('template.txt'));
		$this->assertFalse($this->configStorage->hasTemplate('not_exists.txt'));
	}

	public function testHasTemplate_Template()
	{
		$this->configStorage->create();

		$templatePath = Path::from($this->testDir, '/templates/template.txt');
		$file = File::fromPath($templatePath);
		$file->write('test content');
		$template = Template::fromFile($file);

		$this->assertTrue($this->configStorage->hasTemplate($template));
	}
}
