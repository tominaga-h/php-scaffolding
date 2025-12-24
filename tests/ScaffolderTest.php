<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Hytmng\PhpScff\Scaffolder;
use Hytmng\PhpScff\Group;
use Hytmng\PhpScff\Tree\TreeEntry;
use Hytmng\PhpScff\Tree\TreeNode;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\Service\TwigService;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ScaffolderTest extends TestCase
{
    private string $testDir;
    private Filesystem $filesystem;
    private Group&MockObject $group;
    private TwigService&MockObject $twigService;
    private OutputInterface&MockObject $output;

    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/php-scff-scaffolder-test';
        $this->filesystem = new Filesystem();

        // テストディレクトリを作成
        if (!file_exists($this->testDir)) {
            $this->filesystem->mkdir($this->testDir);
        }

        // Groupのモックを作成
        $this->group = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->getMock();

        // TwigServiceのモックを作成
        $this->twigService = $this->getMockBuilder(TwigService::class)
            ->disableOriginalConstructor()
            ->getMock();

        // OutputInterfaceのモックを作成
        $this->output = $this->getMockBuilder(OutputInterface::class)
            ->getMock();
    }

    protected function tearDown(): void
    {
        // テストディレクトリを削除
        if (file_exists($this->testDir)) {
            $this->filesystem->remove($this->testDir);
        }
    }

    public function testScaffold_TreeEntry_CreatesDirectory(): void
    {
        $subDir = $this->testDir . '/new-directory';
        $entry = TreeEntry::fromPath(new Path($subDir));

        // ログ出力を期待
        $this->output->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('new-directory'));

        Scaffolder::scaffold($entry, $this->group, $this->twigService, [], $this->output);

        $this->assertDirectoryExists($subDir);
    }

    public function testScaffold_TreeNode_WithTemplate_CreatesFile(): void
    {
        // テスト用ディレクトリを事前に作成
        $this->filesystem->mkdir($this->testDir . '/files');
        $filePath = $this->testDir . '/files/test.php';
        $file = new File(new Path($filePath), $this->filesystem);
        $node = new TreeNode($file);

        // Group::hasTemplateがtrueを返すように設定
        $this->group->expects($this->once())
            ->method('hasTemplate')
            ->with('test.php')
            ->willReturn(true);

        // TwigService::renderが内容を返すように設定
        $expectedContent = '<?php echo "Hello World";';
        $this->twigService->expects($this->once())
            ->method('render')
            ->with('test.php', [])
            ->willReturn($expectedContent);

        // ログ出力を期待
        $this->output->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('test.php'));

        Scaffolder::scaffold($node, $this->group, $this->twigService, [], $this->output);

        $this->assertFileExists($filePath);
        $this->assertEquals($expectedContent, file_get_contents($filePath));
    }

    public function testScaffold_TreeNode_WithoutTemplate_DoesNothing(): void
    {
        $filePath = $this->testDir . '/no-template.php';
        $file = new File(new Path($filePath), $this->filesystem);
        $node = new TreeNode($file);

        // Group::hasTemplateがfalseを返すように設定
        $this->group->expects($this->once())
            ->method('hasTemplate')
            ->with('no-template.php')
            ->willReturn(false);

        // TwigService::renderは呼ばれない
        $this->twigService->expects($this->never())
            ->method('render');

        // 出力も呼ばれない
        $this->output->expects($this->never())
            ->method('writeln');

        Scaffolder::scaffold($node, $this->group, $this->twigService, [], $this->output);

        $this->assertFileDoesNotExist($filePath);
    }

    public function testScaffold_TreeEntry_WithChildNodes_RecursivelyProcesses(): void
    {
        // 親ディレクトリのエントリを作成
        $parentDir = $this->testDir . '/parent';
        $parentEntry = TreeEntry::fromPath(new Path($parentDir));

        // 子ファイルのノードを作成
        $childFilePath = $parentDir . '/child.php';
        $childFile = new File(new Path($childFilePath), $this->filesystem);
        $childNode = new TreeNode($childFile);
        $parentEntry->addNode($childNode);

        // Group::hasTemplateがtrueを返すように設定
        $this->group->expects($this->once())
            ->method('hasTemplate')
            ->with('child.php')
            ->willReturn(true);

        // TwigService::renderが内容を返すように設定
        $expectedContent = '<?php // child content';
        $this->twigService->expects($this->once())
            ->method('render')
            ->with('child.php', ['key' => 'value'])
            ->willReturn($expectedContent);

        // ログ出力が2回呼ばれることを期待（ディレクトリとファイル）
        $this->output->expects($this->exactly(2))
            ->method('writeln');

        Scaffolder::scaffold($parentEntry, $this->group, $this->twigService, ['key' => 'value'], $this->output);

        // 親ディレクトリが作成されている
        $this->assertDirectoryExists($parentDir);
        // 子ファイルが作成されている
        $this->assertFileExists($childFilePath);
        $this->assertEquals($expectedContent, file_get_contents($childFilePath));
    }

    public function testScaffold_TreeEntry_WithNestedTreeEntry_CreatesNestedDirectories(): void
    {
        // ルートディレクトリ
        $rootDir = $this->testDir . '/root';
        $rootEntry = TreeEntry::fromPath(new Path($rootDir));

        // サブディレクトリ
        $subDir = $rootDir . '/sub';
        $subEntry = TreeEntry::fromPath(new Path($subDir));

        // 孫ディレクトリ
        $grandchildDir = $subDir . '/grandchild';
        $grandchildEntry = TreeEntry::fromPath(new Path($grandchildDir));

        // ネスト構造を構築
        $subEntry->addNode($grandchildEntry);
        $rootEntry->addNode($subEntry);

        // ログ出力が3回呼ばれることを期待（3つのディレクトリ）
        $this->output->expects($this->exactly(3))
            ->method('writeln');

        Scaffolder::scaffold($rootEntry, $this->group, $this->twigService, [], $this->output);

        // すべてのディレクトリが作成されている
        $this->assertDirectoryExists($rootDir);
        $this->assertDirectoryExists($subDir);
        $this->assertDirectoryExists($grandchildDir);
    }

    public function testScaffold_ThrowsException_WhenRenderFails(): void
    {
        // テスト用ディレクトリを事前に作成
        $this->filesystem->mkdir($this->testDir . '/error-test');
        $filePath = $this->testDir . '/error-test/error.php';
        $file = new File(new Path($filePath), $this->filesystem);
        $node = new TreeNode($file);

        // Group::hasTemplateがtrueを返すように設定
        $this->group->expects($this->once())
            ->method('hasTemplate')
            ->with('error.php')
            ->willReturn(true);

        // TwigService::renderが例外をスローするように設定
        $this->twigService->expects($this->once())
            ->method('render')
            ->with('error.php', [])
            ->willThrowException(new \Exception('Template rendering failed'));

        // ログ出力を期待（エラーログ）
        $this->output->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('error.php'));

        // 例外がスローされることを期待
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to scaffold');

        Scaffolder::scaffold($node, $this->group, $this->twigService, [], $this->output);
    }
}

