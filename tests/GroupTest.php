<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\Template;
use Hytmng\PhpScff\Group;
use Hytmng\PhpScff\Exception\ExistenceException;
use Symfony\Component\Filesystem\Filesystem;

class GroupTest extends TestCase
{
    private string $testDir;
    private ?string $srcDir = null;
    private Group $group;
    private Filesystem $fs;

    protected function setUp(): void
    {
        $this->fs = new Filesystem();
        $this->testDir = sys_get_temp_dir() . '/phpscff_group_' . uniqid();
        $dir = Directory::fromStringPath($this->testDir);
        $this->group = new Group($dir);
    }

    protected function tearDown(): void
    {
        if ($this->fs->exists($this->testDir)) {
            $this->fs->remove($this->testDir);
        }
        if ($this->srcDir !== null && $this->fs->exists($this->srcDir)) {
            $this->fs->remove($this->srcDir);
        }
    }

    public function testExistsDefaultFalse(): void
    {
        $this->assertFalse($this->group->exists());
    }

    public function testCreateAndRemove(): void
    {
        $this->group->create();
        $this->assertTrue($this->group->exists());

        // duplicate create throws
        $this->expectException(ExistenceException::class);
        $this->expectExceptionMessage('Directory "' . $this->testDir . '" is already exists');
        $this->group->create();
    }

    public function testRemoveThrowsWhenNotExists(): void
    {
        $this->expectException(ExistenceException::class);
        $this->expectExceptionMessage('Directory "' . $this->testDir . '" is not exists');
        $this->group->remove();
    }

    public function testAddTemplateAutoCreatesGroup(): void
    {
        $this->assertFalse($this->group->exists());

        // prepare source file outside group dir
        $this->srcDir = sys_get_temp_dir() . '/phpscff_group_src_' . uniqid();
        $srcFilePath = $this->srcDir . '/template.txt';
        $file = File::fromStringPath($srcFilePath);
        $file->write('test content');

        $template = Template::fromFile($file);

        // add without explicit create
        $this->group->addTemplate($template);
        $this->assertTrue($this->group->exists());
        $this->assertTrue($this->group->hasTemplate('template.txt'));

        // check file copied
        $dest = $this->testDir . '/template.txt';
        $this->assertTrue($this->fs->exists($dest));
        $this->assertEquals('test content', file_get_contents($dest));
    }

    public function testAddAndGetAndHasTemplates(): void
    {
        $this->group->create();

        // prepare source file
        $this->srcDir = sys_get_temp_dir() . '/phpscff_group_src_' . uniqid();
        $srcFilePath = $this->srcDir . '/t1.txt';
        $file = File::fromStringPath($srcFilePath);
        $file->write('content1');

        $template = Template::fromFile($file);

        $this->assertFalse($this->group->hasTemplate('t1.txt'));
        $this->group->addTemplate($template);
        $this->assertTrue($this->group->hasTemplate($template));
        $this->assertTrue($this->group->hasTemplate('t1.txt'));

        $tpl = $this->group->getTemplate('t1.txt');
        $this->assertInstanceOf(Template::class, $tpl);
        $this->assertEquals('t1.txt', $tpl->getFilename());

        $all = $this->group->getTemplates();
        $this->assertCount(1, $all);
        $this->assertEquals('t1.txt', $all[0]->getFilename());
    }

    public function testAddDuplicateTemplateThrows(): void
    {
        $this->group->create();
        $this->srcDir = sys_get_temp_dir() . '/phpscff_group_src_' . uniqid();
        $path = $this->srcDir . '/dup.txt';
        $file = File::fromStringPath($path);
        $file->write('foo');
        $template = Template::fromFile($file);

        $this->group->addTemplate($template);
        $this->expectException(ExistenceException::class);
        $this->expectExceptionMessage('Template "dup.txt" is already exists.');
        $this->group->addTemplate($template);
    }

    public function testGetTemplatesThrowsWhenGroupNotExists(): void
    {
        $this->expectException(ExistenceException::class);
        $this->expectExceptionMessage('Group "' . $this->group->getGroupName() . '" is not exists');
        $this->group->getTemplates();
    }

    public function testGetTemplateThrowsWhenTemplateNotExists(): void
    {
        $this->group->create();
        $this->expectException(ExistenceException::class);
        $this->expectExceptionMessage('Template "none.txt" is not exists.');
        $this->group->getTemplate('none.txt');
    }

    public function testHasTemplateReturnsFalseWhenNoDirectory(): void
    {
        $this->assertFalse($this->group->hasTemplate('foo.txt'));
    }
}
