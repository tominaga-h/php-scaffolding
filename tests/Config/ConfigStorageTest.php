<?php

namespace Tests\Config;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\Config\ConfigStorage;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\Template;
use Hytmng\PhpScff\Exception\ExistenceException;
use Symfony\Component\Filesystem\Filesystem;

class ConfigStorageTest extends TestCase
{
    private string $testDir;
    private ConfigStorage $configStorage;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/phpscff_test_' . uniqid();
        $this->configStorage = new ConfigStorage($this->testDir);
        $this->filesystem = new Filesystem();
    }

    protected function tearDown(): void
    {
        if ($this->filesystem->exists($this->testDir)) {
            $this->filesystem->remove($this->testDir);
        }
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(ConfigStorage::class, $this->configStorage);
    }

    public function testGetConfigDir(): void
    {
        $configDir = $this->configStorage->getConfigDir();
        $this->assertInstanceOf(Directory::class, $configDir);
        $this->assertEquals($this->testDir . '/.phpscff', $configDir->getStringPath());
    }

    public function testGetTemplateDir(): void
    {
        $templateDir = $this->configStorage->getTemplateDir();
        $this->assertInstanceOf(Directory::class, $templateDir);
        $this->assertEquals($this->testDir . '/.phpscff/templates', $templateDir->getStringPath());
    }

    public function testExists(): void
    {
        $this->assertFalse($this->configStorage->exists());
        $this->configStorage->create();
        $this->assertTrue($this->configStorage->exists());
    }

    public function testCreate(): void
    {
        $this->configStorage->create();
        $this->assertTrue($this->filesystem->exists($this->testDir . '/.phpscff'));
        $this->assertTrue($this->filesystem->exists($this->testDir . '/.phpscff/templates'));
    }

    public function testCreate_ThrowException(): void
    {
        $this->configStorage->create();
        $this->expectException(ExistenceException::class);
        $this->configStorage->create();
    }

    public function testRemove(): void
    {
        $this->configStorage->create();
        $this->configStorage->remove();
        $this->assertFalse($this->filesystem->exists($this->testDir . '/.phpscff'));
    }

    public function testRemove_ThrowException(): void
    {
        $this->expectException(ExistenceException::class);
        $this->configStorage->remove();
    }

    public function testAddTemplate(): void
    {
        $this->configStorage->create();
        $templateFile = File::fromStringPath($this->testDir . '/test_template.txt');
        $templateFile->write('test content');
        $template = Template::fromFile($templateFile);

        $this->configStorage->addTemplate($template);
        $this->assertTrue($this->filesystem->exists($this->testDir . '/.phpscff/templates/test_template.txt'));
    }

    public function testAddTemplate_ThrowException(): void
    {
        $this->configStorage->create();
        $templateFile = File::fromStringPath($this->testDir . '/test_template.txt');
        $templateFile->write('test content');
        $template = Template::fromFile($templateFile);

        $this->configStorage->addTemplate($template);
        $this->expectException(ExistenceException::class);
        $this->configStorage->addTemplate($template);
    }

    public function testGetTemplate(): void
    {
        $this->configStorage->create();
        $templateFile = File::fromStringPath($this->testDir . '/test_template.txt');
        $templateFile->write('test content');
        $template = Template::fromFile($templateFile);

        $this->configStorage->addTemplate($template);
        $retrievedTemplate = $this->configStorage->getTemplate('test_template.txt');
        $this->assertInstanceOf(Template::class, $retrievedTemplate);
        $this->assertEquals('test_template.txt', $retrievedTemplate->getFilename());
    }

    public function testGetTemplate_ThrowException(): void
    {
        $this->configStorage->create();
        $this->expectException(ExistenceException::class);
        $this->configStorage->getTemplate('non_existent.txt');
    }

    public function testHasTemplate(): void
    {
        $this->configStorage->create();
        $templateFile = File::fromStringPath($this->testDir . '/test_template.txt');
        $templateFile->write('test content');
        $template = Template::fromFile($templateFile);

        $this->assertFalse($this->configStorage->hasTemplate('test_template.txt'));
        $this->configStorage->addTemplate($template);
        $this->assertTrue($this->configStorage->hasTemplate('test_template.txt'));
    }

    public function testGetGroups(): void
    {
        $this->configStorage->create();
        $this->filesystem->mkdir($this->testDir . '/.phpscff/templates/group1');
        $this->filesystem->mkdir($this->testDir . '/.phpscff/templates/group2');

        $groups = $this->configStorage->getGroups();
        $this->assertCount(2, $groups);
        $this->assertContains('group1', $groups);
        $this->assertContains('group2', $groups);
    }

    public function testHasGroup(): void
    {
        $this->configStorage->create();
        $this->filesystem->mkdir($this->testDir . '/.phpscff/templates/group1');

        $this->assertTrue($this->configStorage->hasGroup('group1'));
        $this->assertFalse($this->configStorage->hasGroup('non_existent'));
    }

    public function testSetPathResolver(): void
    {
        $newPath = $this->testDir . '/new_path';
        $newResolver = new \Hytmng\PhpScff\Config\PathResolver($newPath, '.phpscff');
        $this->configStorage->setPathResolver($newResolver);

        $configDir = $this->configStorage->getConfigDir();
        $this->assertEquals($newPath, $configDir->getStringPath());
    }

    public function testGetTemplates_WithGroup(): void
    {
        $this->configStorage->create();
        $this->filesystem->mkdir($this->testDir . '/.phpscff/templates/group1');

        $templateFile = File::fromStringPath($this->testDir . '/test_template.txt');
        $templateFile->write('test content');
        $template = Template::fromFile($templateFile);

        $this->configStorage->addTemplate($template, 'group1');

        $templates = $this->configStorage->getTemplates('group1');
        $this->assertCount(1, $templates);
        $this->assertEquals('test_template.txt', $templates[0]->getFilename());
    }

    public function testGetTemplates_WithNonExistentGroup(): void
    {
        $this->configStorage->create();
        $this->expectException(ExistenceException::class);
        $this->configStorage->getTemplates('non_existent');
    }

    public function testGetTemplatesByGroup(): void
    {
        $this->configStorage->create();
        $this->filesystem->mkdir($this->testDir . '/.phpscff/templates/group1');
        $this->filesystem->mkdir($this->testDir . '/.phpscff/templates/group2');

        $templateFile1 = File::fromStringPath($this->testDir . '/test_template1.txt');
        $templateFile1->write('test content 1');
        $template1 = Template::fromFile($templateFile1);

        $templateFile2 = File::fromStringPath($this->testDir . '/test_template2.txt');
        $templateFile2->write('test content 2');
        $template2 = Template::fromFile($templateFile2);

        $this->configStorage->addTemplate($template1, 'group1');
        $this->configStorage->addTemplate($template2, 'group2');

        $templatesByGroup = $this->configStorage->getTemplatesByGroup();
        $this->assertCount(2, $templatesByGroup);
        $this->assertArrayHasKey('group1', $templatesByGroup);
        $this->assertArrayHasKey('group2', $templatesByGroup);
        $this->assertCount(1, $templatesByGroup['group1']);
        $this->assertCount(1, $templatesByGroup['group2']);
    }

    public function testGetGroup(): void
    {
        $this->configStorage->create();
        $this->filesystem->mkdir($this->testDir . '/.phpscff/templates/group1');

        $group = $this->configStorage->getGroup('group1');
        $this->assertInstanceOf(\Hytmng\PhpScff\Group::class, $group);
    }

    public function testHasTemplate_WithGroup(): void
    {
        $this->configStorage->create();
        $this->filesystem->mkdir($this->testDir . '/.phpscff/templates/group1');

        $templateFile = File::fromStringPath($this->testDir . '/test_template.txt');
        $templateFile->write('test content');
        $template = Template::fromFile($templateFile);

        $this->assertFalse($this->configStorage->hasTemplate('test_template.txt', 'group1'));
        $this->configStorage->addTemplate($template, 'group1');
        $this->assertTrue($this->configStorage->hasTemplate('test_template.txt', 'group1'));
    }

    public function testHasTemplate_WithTemplateObject(): void
    {
        $this->configStorage->create();
        $templateFile = File::fromStringPath($this->testDir . '/test_template.txt');
        $templateFile->write('test content');
        $template = Template::fromFile($templateFile);

        $this->assertFalse($this->configStorage->hasTemplate($template));
        $this->configStorage->addTemplate($template);
        $this->assertTrue($this->configStorage->hasTemplate($template));
    }

    public function testHasTemplate_ThrowException(): void
    {
        $this->expectException(ExistenceException::class);
        $this->configStorage->hasTemplate('test_template.txt');
    }
}
