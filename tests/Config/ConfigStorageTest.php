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
	}

	public function testRemove()
	{
		$this->configStorage->create();
		$this->assertTrue($this->configStorage->exists());

		$configDir = $this->configStorage->getConfigDir();
		$this->assertTrue($configDir->exists());

        $templateDir = $this->configStorage->getTemplateDir();
        $this->assertTrue($templateDir->exists());

		$this->configStorage->remove();
		$this->assertFalse($this->configStorage->exists());

        $this->assertFalse($configDir->exists());
        $this->assertFalse($templateDir->exists());

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

    public function testAddTemplateWithGroup(): void
    {
        // 設定ディレクトリを作成
        $this->configStorage->create();

        // テンプレートファイルを作成
        $templatePath = $this->testDir . '/template.txt';
        $file = File::fromStringPath($templatePath);
        $file->write('test content');
        $template = new Template($file, new Filesystem());

        // グループ付きでテンプレートを追加
        $group = 'group1';
        $this->configStorage->addTemplate($template, $group);

        // グループディレクトリにコピーされていることを確認
        $templateDir = $this->configStorage->getTemplateDir();
        $groupDir = $templateDir->getSubDir($group);
        $this->assertTrue($groupDir->exists());

        $expectedPath = $groupDir->getStringPath() . '/template.txt';
        $this->assertTrue($this->filesystem->exists($expectedPath));
        $this->assertEquals('test content', file_get_contents($expectedPath));

        // hasTemplate のグループチェック
        $this->assertTrue($this->configStorage->hasTemplate('template.txt', $group));
        $this->assertFalse($this->configStorage->hasTemplate('template.txt', 'otherGroup'));
    }

    public function testAddTemplateWithGroupDuplicate(): void
    {
        $this->configStorage->create();

        $templatePath = $this->testDir . '/template.txt';
        $file = File::fromStringPath($templatePath);
        $file->write('test content');
        $template = new Template($file, new Filesystem());

        $group = 'group1';
        $this->configStorage->addTemplate($template, $group);

        // 同一グループ内で重複エラー
        $this->expectException(ExistenceException::class);
        $this->expectExceptionMessage('Template "template.txt" is already exists.');
        $this->configStorage->addTemplate($template, $group);
    }

    public function testHasTemplateWithoutGroupWhenGroupExists(): void
    {
        $this->configStorage->create();

        $templatePath = $this->testDir . '/template.txt';
        $file = File::fromStringPath($templatePath);
        $file->write('test content');
        $template = new Template($file, new Filesystem());

        $group = 'group1';
        $this->configStorage->addTemplate($template, $group);

        // グループ指定なしでは見つからない
        $this->assertFalse($this->configStorage->hasTemplate('template.txt'));
    }

    public function testGetTemplatesWithGroup(): void
    {
        $this->configStorage->create();

        // グループ1のテンプレート作成
        $group1 = 'group1';
        $template1Path = $this->testDir . '/template1.txt';
        $file1 = File::fromStringPath($template1Path);
        $file1->write('content1');
        // 新しいファイル名でTemplateオブジェクトを作成
        $newFile1 = File::fromStringPath($this->testDir . '/template.txt');
        $newFile1->write('content1', true);
        $template1 = new Template($newFile1, new Filesystem());
        $this->configStorage->addTemplate($template1, $group1);

        // グループ2のテンプレート作成
        $group2 = 'group2';
        $template2Path = $this->testDir . '/template2.txt';
        $file2 = File::fromStringPath($template2Path);
        $file2->write('content2');
        // 新しいファイル名でTemplateオブジェクトを作成
        $newFile2 = File::fromStringPath($this->testDir . '/template.txt');
        $newFile2->write('content2', true);
        $template2 = new Template($newFile2, new Filesystem());
        $this->configStorage->addTemplate($template2, $group2);

        // グループ1のテンプレートのみ取得
        $templates = $this->configStorage->getTemplates($group1);
        $this->assertCount(1, $templates);
        $this->assertEquals('template.txt', $templates[0]->getFilename());

        // 存在しないグループを指定した場合は例外
        $this->expectException(ExistenceException::class);
        $this->expectExceptionMessage('Group "not_exists" is not exists');
        $this->configStorage->getTemplates('not_exists');
    }

    public function testGetTemplateWithGroup(): void
    {
        $this->configStorage->create();

        // 2つのグループに同じ名前のテンプレートを作成
        $group1 = 'group1';
        $group2 = 'group2';

        // グループ1のテンプレート
        $template1Path = $this->testDir . '/template1.txt';
        $file1 = File::fromStringPath($template1Path);
        $file1->write('content1');
        // 新しいファイル名でTemplateオブジェクトを作成
        $newFile1 = File::fromStringPath($this->testDir . '/template.txt');
        $newFile1->write('content1', true);
        $template1 = new Template($newFile1, new Filesystem());
        $this->configStorage->addTemplate($template1, $group1);

        // グループ2のテンプレート（同じ名前で異なるファイル）
        $template2Path = $this->testDir . '/template2.txt';
        $file2 = File::fromStringPath($template2Path);
        $file2->write('content2');
        // 新しいファイル名でTemplateオブジェクトを作成
        $newFile2 = File::fromStringPath($this->testDir . '/template.txt');
        $newFile2->write('content2', true);
        $template2 = new Template($newFile2, new Filesystem());
        $this->configStorage->addTemplate($template2, $group2);

        // グループを指定してテンプレートを取得
        $template = $this->configStorage->getTemplate('template.txt', $group1);
        $this->assertEquals('template.txt', $template->getFilename());
        $templateDir = $this->configStorage->getTemplateDir();
        $groupDir = $templateDir->getSubDir($group1);
        $this->assertEquals('content1', file_get_contents($groupDir->getFile('template.txt')->getStringPath()));

        // 存在しないグループを指定した場合は例外
        $this->expectException(ExistenceException::class);
        $this->expectExceptionMessage('Group "not_exists" is not exists');
        $this->configStorage->getTemplate('template.txt', 'not_exists');
    }

    public function testGetTemplateNotFoundInGroup(): void
    {
        $this->configStorage->create();

        // グループを作成
        $group = 'group1';
        $templatePath = $this->testDir . '/template1.txt';
        $file = File::fromStringPath($templatePath);
        $file->write('content1');
        $template = new Template($file, new Filesystem());
        $this->configStorage->addTemplate($template, $group);

        // 存在しないテンプレートを指定した場合は例外
        $this->expectException(ExistenceException::class);
        $this->expectExceptionMessage('Template "not_exists.txt" is not exists.');
        $this->configStorage->getTemplate('not_exists.txt', $group);
    }

    public function testGetTemplatesByGroup(): void
    {
        $this->configStorage->create();

        // グループ1のテンプレート作成
        $group1 = 'group1';
        $template1Path = $this->testDir . '/template1.txt';
        $file1 = File::fromStringPath($template1Path);
        $file1->write('content1');
        $template1 = new Template($file1, new Filesystem());
        $this->configStorage->addTemplate($template1, $group1);

        // グループ2のテンプレート作成（2つのファイル）
        $group2 = 'group2';
        $template2Path = $this->testDir . '/template2.txt';
        $file2 = File::fromStringPath($template2Path);
        $file2->write('content2');
        $template2 = new Template($file2, new Filesystem());
        $this->configStorage->addTemplate($template2, $group2);

        $template3Path = $this->testDir . '/template3.txt';
        $file3 = File::fromStringPath($template3Path);
        $file3->write('content3');
        $template3 = new Template($file3, new Filesystem());
        $this->configStorage->addTemplate($template3, $group2);

        // 空のグループ3を作成
        $group3 = 'group3';
        $templateDir = $this->configStorage->getTemplateDir();
        $groupDir3 = $templateDir->getSubDir($group3);
        $groupDir3->create();

        // グループごとのテンプレート取得
        $templatesByGroup = $this->configStorage->getTemplatesByGroup();

        // 検証
        $this->assertCount(2, $templatesByGroup); // 空のグループは含まれない
        $this->assertArrayHasKey($group1, $templatesByGroup);
        $this->assertArrayHasKey($group2, $templatesByGroup);

        // グループ1の検証
        $this->assertCount(1, $templatesByGroup[$group1]);
        $this->assertEquals('template1.txt', $templatesByGroup[$group1][0]->getFilename());

        // グループ2の検証
        $this->assertCount(2, $templatesByGroup[$group2]);
        $filenames = array_map(function($template) {
            return $template->getFilename();
        }, $templatesByGroup[$group2]);
        $this->assertContains('template2.txt', $filenames);
        $this->assertContains('template3.txt', $filenames);
    }

    public function testGetGroups(): void
    {
        $this->configStorage->create();

        // グループディレクトリを作成
        $templateDir = $this->configStorage->getTemplateDir();
        $groups = ['group1', 'group2', 'group3'];
        foreach ($groups as $group) {
            $groupDir = $templateDir->getSubDir($group);
            $groupDir->create();
        }

        // ファイルも作成（グループではない）
        $filePath = $this->testDir . '/templates/file.txt';
        $file = File::fromStringPath($filePath);
        $file->write('content');

        // グループ一覧を取得
        $actualGroups = $this->configStorage->getGroups();

        // 検証
        $this->assertCount(3, $actualGroups);
        foreach ($groups as $group) {
            $this->assertContains($group, $actualGroups);
        }
    }

    public function testHasGroup(): void
    {
        $this->configStorage->create();

        // グループディレクトリを作成
        $templateDir = $this->configStorage->getTemplateDir();
        $groupDir = $templateDir->getSubDir('testgroup');
        $groupDir->create();

        // 存在するグループの確認
        $this->assertTrue($this->configStorage->hasGroup('testgroup'));

        // 存在しないグループの確認
        $this->assertFalse($this->configStorage->hasGroup('nonexistent'));

        // 複数のグループを作成して確認
        $groups = ['group1', 'group2', 'group3'];
        foreach ($groups as $group) {
            $groupDir = $templateDir->getSubDir($group);
            $groupDir->create();
        }

        foreach ($groups as $group) {
            $this->assertTrue($this->configStorage->hasGroup($group));
        }

        // ファイルは含まれないことを確認
        $filePath = $this->testDir . '/templates/file.txt';
        $file = File::fromStringPath($filePath);
        $file->write('content');
        $this->assertFalse($this->configStorage->hasGroup('file.txt'));
    }
}
