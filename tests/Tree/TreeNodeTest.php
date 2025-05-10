<?php

namespace Tests\Tree;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\Tree\TreeNode;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\FileSystem\Path;
use Symfony\Component\Filesystem\Filesystem;

class TreeNodeTest extends TestCase
{
    private string $testDir;
    private string $testFile;

    protected function setUp(): void
    {
        $this->testDir = sys_get_temp_dir() . '/php-scff-test';
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
        if (file_exists($this->testDir)) {
            rmdir($this->testDir);
        }
    }

    public function testFromPath(): void
    {
        $path = new Path($this->testFile);
        $node = TreeNode::fromPath($path);

        $this->assertInstanceOf(TreeNode::class, $node);
        $this->assertEquals('test.txt', (string)$node);
    }

    public function testCreate(): void
    {
        $path = new Path($this->testFile);
        $node = TreeNode::fromPath($path);

        $content = 'Hello, World!';
        $node->create($content);

        $this->assertFileExists($this->testFile);
        $this->assertEquals($content, file_get_contents($this->testFile));
    }

    public function testToString(): void
    {
        $file = new File(new Path($this->testFile), new Filesystem());
        $node = new TreeNode($file);

        $this->assertEquals('test.txt', (string)$node);
    }
}
