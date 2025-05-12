<?php

namespace Tests\Tree;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\Tree\StructureParser;
use Hytmng\PhpScff\Tree\TreeEntry;
use Hytmng\PhpScff\Tree\TreeNode;

class StructureParserTest extends TestCase
{
    public function testParse_SimpleStructure(): void
    {
        $structure = [
            'root' => [
                'file1.txt',
                'file2.txt'
            ]
        ];

        $rootEntry = StructureParser::parse($structure, 'test-dir');

        $this->assertInstanceOf(TreeEntry::class, $rootEntry);
        $expected = "test-dir\n├── file1.txt\n└── file2.txt";
        $this->assertEquals($expected, (string)$rootEntry);

        $nodes = $rootEntry->getNodes();
        $this->assertCount(2, $nodes);
        $this->assertInstanceOf(TreeNode::class, $nodes[0]);
        $this->assertInstanceOf(TreeNode::class, $nodes[1]);
        $this->assertEquals('file1.txt', (string)$nodes[0]);
        $this->assertEquals('file2.txt', (string)$nodes[1]);
    }

    public function testParse_NestedStructure(): void
    {
        $structure = [
            'root' => [
                'file1.txt',
                [
                    'subdir' => [
                        'file2.txt',
                        'file3.txt'
                    ]
                ]
            ]
        ];

        $rootEntry = StructureParser::parse($structure, 'test-dir');

        $this->assertInstanceOf(TreeEntry::class, $rootEntry);
        $expected = "test-dir\n├── file1.txt\n└── subdir\n    ├── file2.txt\n    └── file3.txt";
        $this->assertEquals($expected, (string)$rootEntry);

        $nodes = $rootEntry->getNodes();
        $this->assertCount(2, $nodes);
        $this->assertInstanceOf(TreeNode::class, $nodes[0]);
        $this->assertInstanceOf(TreeEntry::class, $nodes[1]);

        $subdir = $nodes[1];
        $expected = "subdir\n├── file2.txt\n└── file3.txt";
        $this->assertEquals($expected, (string)$subdir);

        $subdirNodes = $subdir->getNodes();
        $this->assertCount(2, $subdirNodes);
        $this->assertInstanceOf(TreeNode::class, $subdirNodes[0]);
        $this->assertInstanceOf(TreeNode::class, $subdirNodes[1]);
        $this->assertEquals('file2.txt', (string)$subdirNodes[0]);
        $this->assertEquals('file3.txt', (string)$subdirNodes[1]);
    }

    public function testParse_DeeplyNestedStructure(): void
    {
        $structure = [
            'root' => [
                'file1.txt',
                [
                    'subdir1' => [
                        'file2.txt',
                        [
                            'subdir2' => [
                                'file3.txt'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $rootEntry = StructureParser::parse($structure, 'test-dir');

        $this->assertInstanceOf(TreeEntry::class, $rootEntry);
        $expected = "test-dir\n├── file1.txt\n└── subdir1\n    ├── file2.txt\n    └── subdir2\n        └── file3.txt";
        $this->assertEquals($expected, (string)$rootEntry);

        $nodes = $rootEntry->getNodes();
        $this->assertCount(2, $nodes);

        $subdir1 = $nodes[1];
        $expected = "subdir1\n├── file2.txt\n└── subdir2\n    └── file3.txt";
        $this->assertEquals($expected, (string)$subdir1);

        $subdir1Nodes = $subdir1->getNodes();
        $this->assertCount(2, $subdir1Nodes);

        $subdir2 = $subdir1Nodes[1];
        $expected = "subdir2\n└── file3.txt";
        $this->assertEquals($expected, (string)$subdir2);

        $subdir2Nodes = $subdir2->getNodes();
        $this->assertCount(1, $subdir2Nodes);
        $this->assertEquals('file3.txt', (string)$subdir2Nodes[0]);
    }

    public function testParse_ThrowException_InvalidItem(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid item format in structure');

        $structure = [
            'root' => [
                123 // 数値は無効な形式
            ]
        ];

        StructureParser::parse($structure, 'test-dir');
    }

    public function testParse_ThrowException_UndefinedStructure(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Directory structure is not defined.\nPlease edit that by `group:config` command.");

        $structure = ['root' => null];
        StructureParser::parse($structure, 'test-dir');
    }
}
