<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\Template;
use Hytmng\PhpScff\Group;
use Hytmng\PhpScff\Exception\ExistenceException;
use Hytmng\PhpScff\Process\EditProcess;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Filesystem\Filesystem;

class GroupTest extends TestCase
{
    private string $testDir;
    private Filesystem $filesystem;
    private Group $group;
    private EditProcess&MockObject $editProcess;

    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/php-scff-test';
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->testDir);

        // EditProcessのモックを作成
		$this->editProcess = $this->getMockBuilder(EditProcess::class)
        ->disableOriginalConstructor()
        ->getMock();

        // Groupオブジェクトを作成
        $directory = new Directory(Path::from($this->testDir, 'test-group'), $this->filesystem);
        $this->group = new Group($directory);
        $this->group->setEditProcess($this->editProcess);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->testDir);
    }

    public function testGetGroupName(): void
    {
        $this->assertEquals('test-group', $this->group->getGroupName());
    }

    public function testExists_WhenNotExists(): void
    {
        $this->assertFalse($this->group->exists());
    }

    public function testExists_WhenExists(): void
    {
        $this->group->create();
        $this->assertTrue($this->group->exists());
    }

    public function testCreate(): void
    {
        $this->group->create();
        $this->assertTrue($this->group->exists());
        $this->assertFileExists($this->testDir . '/test-group/meta.yaml');
    }

    public function testRemove(): void
    {
        $this->group->create();
        $this->group->remove();
        $this->assertFalse($this->group->exists());
    }

    public function testRename(): void
    {
        $this->group->create();
        $this->group->rename('new-group');
        $this->assertEquals('new-group', $this->group->getGroupName());
        $this->assertDirectoryExists($this->testDir . '/new-group');
    }

    public function testAddTemplate(): void
    {
        $this->group->create();
        $file = File::fromStringPath($this->testDir . '/template.php');
        $file->write('<?php echo "test";');
        $template = Template::fromFile($file);

        $this->group->addTemplate($template);
        $this->assertTrue($this->group->hasTemplate('template.php'));
    }

    public function testAddTemplate_ThrowException_WhenTemplateExists(): void
    {
        $this->group->create();
        $file = File::fromStringPath($this->testDir . '/template.php');
        $file->write('<?php echo "test";');
        $template = Template::fromFile($file);

        $this->group->addTemplate($template);
        $this->expectException(ExistenceException::class);
        $this->group->addTemplate($template);
    }

    public function testAddTemplate_CreateGroupIfNotExists(): void
    {
        $file = File::fromStringPath($this->testDir . '/template.php');
        $file->write('<?php echo "test";');
        $template = Template::fromFile($file);

        $this->assertFalse($this->group->exists());
        $this->group->addTemplate($template);
        $this->assertTrue($this->group->exists());
        $this->assertTrue($this->group->hasTemplate('template.php'));
    }

    public function testGetTemplates(): void
    {
        $this->group->create();
        $file = File::fromStringPath($this->testDir . '/template.php');
        $file->write('<?php echo "test";');
        $template = Template::fromFile($file);

        $this->group->addTemplate($template);
        $templates = $this->group->getTemplates();

        $this->assertCount(1, $templates);
        $this->assertInstanceOf(Template::class, $templates[0]);
    }

    public function testGetTemplates_ThrowException_WhenGroupNotExists(): void
    {
        $this->expectException(ExistenceException::class);
        $this->group->getTemplates();
    }

    public function testGetTemplate(): void
    {
        $this->group->create();
        $file = File::fromStringPath($this->testDir . '/template.php');
        $file->write('<?php echo "test";');
        $template = Template::fromFile($file);

        $this->group->addTemplate($template);
        $retrievedTemplate = $this->group->getTemplate('template.php');

        $this->assertInstanceOf(Template::class, $retrievedTemplate);
        $this->assertEquals('template.php', $retrievedTemplate->getFilename());
    }

    public function testGetTemplate_ThrowException_WhenTemplateNotExists(): void
    {
        $this->group->create();
        $this->expectException(ExistenceException::class);
        $this->group->getTemplate('non-existent.php');
    }

    public function testHasTemplate(): void
    {
        $this->group->create();
        $file = File::fromStringPath($this->testDir . '/template.php');
        $file->write('<?php echo "test";');
        $template = Template::fromFile($file);

        $this->group->addTemplate($template);
        $this->assertTrue($this->group->hasTemplate('template.php'));
        $this->assertFalse($this->group->hasTemplate('non-existent.php'));
    }

    public function testHasTemplate_WhenGroupNotExists(): void
    {
        $this->assertFalse($this->group->hasTemplate('template.php'));
    }

    public function testHasTemplate_WithTemplateInstance(): void
    {
        $this->group->create();
        $file = File::fromStringPath($this->testDir . '/template.php');
        $file->write('<?php echo "test";');
        $template = Template::fromFile($file);

        $this->group->addTemplate($template);
        $this->assertTrue($this->group->hasTemplate($template));
        $this->assertFalse($this->group->hasTemplate(Template::fromFile(File::fromStringPath($this->testDir . '/non-existent.php'))));
    }

    public function testGetTemplate_ThrowException_WhenGroupNotExists(): void
    {
        $this->expectException(ExistenceException::class);
        $this->group->getTemplate('template.php');
    }

    public function testGetMetaYamlPath(): void
    {
        $actual = $this->group->getMetaYamlPath();
        $expected = Path::from($this->testDir, 'test-group', 'meta.yaml');
        $this->assertEquals($expected, $actual);
    }

    public function testEditMetaYaml(): void
    {
        $this->group->create();

		// EditProcessのモックの振る舞いを設定
		$this->editProcess->expects($this->once())
			->method('edit')
			->with($this->group->getMetaYamlPath()->get())
			->willReturn(true);

		// 編集メソッドを実行
		$result = $this->group->editMetaYaml();

		// 編集結果の検証
		$this->assertTrue($result);
    }

}
