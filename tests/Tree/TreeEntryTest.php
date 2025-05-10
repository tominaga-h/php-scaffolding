<?php

namespace Tests\Tree;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\Tree\TreeEntry;
use Hytmng\PhpScff\Tree\TreeNode;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\FileSystem\File;
use Symfony\Component\Filesystem\Filesystem;

class TreeEntryTest extends TestCase
{
    private string $testDir;
    private string $testSubDir;
    private string $testFile;

    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/php-scff-test';
        $this->testSubDir = $this->testDir . '/subdir';
        $this->testFile = $this->testDir . '/test.txt';

        if (!file_exists($this->testDir)) {
            mkdir($this->testDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
        if (file_exists($this->testSubDir)) {
            rmdir($this->testSubDir);
        }
        if (file_exists($this->testDir)) {
            rmdir($this->testDir);
        }
    }

    public function testFromPath(): void
    {
        $path = new Path($this->testDir);
        $entry = TreeEntry::fromPath($path);

        $this->assertInstanceOf(TreeEntry::class, $entry);
    }

    public function testCreate(): void
    {
        $path = new Path($this->testSubDir);
        $entry = TreeEntry::fromPath($path);

        $entry->create();

        $this->assertDirectoryExists($this->testSubDir);
    }

    public function testAddNode(): void
    {
        $path = new Path($this->testDir);
        $entry = TreeEntry::fromPath($path);

        // TreeNodeの追加
        $file = new File(new Path($this->testFile), new Filesystem());
        $node = new TreeNode($file);
        $entry->addNode($node);

        // TreeEntryの追加
        $subEntry = TreeEntry::fromPath(new Path($this->testSubDir));
        $entry->addNode($subEntry);

        $nodes = $entry->getNodes();
        $this->assertCount(2, $nodes);
        $this->assertInstanceOf(TreeNode::class, $nodes[0]);
        $this->assertInstanceOf(TreeEntry::class, $nodes[1]);
    }

    public function testToString(): void
    {
        $path = new Path($this->testDir);
        $entry = TreeEntry::fromPath($path);

        // TreeNodeの追加
        $file = new File(new Path($this->testFile), new Filesystem());
        $node = new TreeNode($file);
        $entry->addNode($node);

        // TreeEntryの追加
        $subEntry = TreeEntry::fromPath(new Path($this->testSubDir));
        $entry->addNode($subEntry);

        $expected = "php-scff-test\n├── test.txt\n└── subdir";
        $this->assertEquals($expected, (string)$entry);
    }
}
