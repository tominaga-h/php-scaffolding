<?php

namespace Hytmng\PhpScff\Tree;

use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\Tree\TreeNode;

/**
 * ツリー構造を保持するための `Directory` クラスのラッパークラス
 */
class TreeEntry
{
	private Directory $directory;

	/** @var array<TreeNode|TreeEntry> $nodes */
	private array $nodes;

	public function __construct(Directory $directory)
	{
		$this->directory = $directory;
	}

	/**
	 * パスから `TreeEntry` を生成する
	 *
	 * @param Path $path
	 * @return TreeEntry
	 */
	public static function fromPath(Path $path): TreeEntry
	{
		$directory = Directory::fromPath($path);
		return new TreeEntry($directory);
	}

	public function create(): void
	{
		$this->directory->create();
	}

	/**
	 * ノードを追加する
	 *
	 * @param TreeNode|TreeEntry $node
	 */
	public function addNode(TreeNode|TreeEntry $node): void
	{
		$this->nodes[] = $node;
	}

	/**
	 * ノードを取得する
	 *
	 * @return array<TreeNode|TreeEntry>
	 */
	public function getNodes(): array
	{
		return $this->nodes;
	}

	/**
	 * ツリー構造を文字列で表現する
	 *
	 * @param int $depth ツリー構造の深さ
	 * @param string $prefix
	 * @param string $branchPrefix
	 * @return string
	 */
	private function renderTree(int $depth = 0, string $prefix = '', string $branchPrefix = ''): string
	{
		$lines = [];
		$lines[] = $branchPrefix . $this->directory->getDirName();

		$nodes = $this->nodes;
		$lastIndex = count($nodes) - 1;

		foreach ($nodes as $i => $node) {
			$isLast = $i === $lastIndex;
			$branch = $isLast ? '└── ' : '├── ';
			$nextPrefix = $prefix . ($isLast ? '    ' : '│   ');
			$branchPrefix = $prefix . $branch;

			if ($node instanceof TreeNode) {
				$lines[] = $branchPrefix . $node;
			} elseif ($node instanceof TreeEntry) {
				$lines[] = $node->renderTree($depth + 1, $nextPrefix, $branchPrefix);
			}
		}

		return implode(PHP_EOL, $lines);
	}

	public function __toString(): string
	{
		return $this->renderTree();
	}

}
