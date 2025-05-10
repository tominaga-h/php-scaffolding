<?php

namespace Hytmng\PhpScff\Tree;

use Hytmng\PhpScff\Tree\TreeEntry;
use Hytmng\PhpScff\Tree\TreeNode;
use Hytmng\PhpScff\FileSystem\Directory;
use Hytmng\PhpScff\FileSystem\Path;

class StructureParser
{
	/**
	 * `meta.yaml` の `structure` データをパースして、 ツリー構造を持つ `TreeEntry` を返す
	 *
	 * @param array<string, mixed> $structure
	 * @param string $rootDir ルートディレクトリの名前
	 * @return TreeEntry
	 */
	public static function parse(array $structure, string $rootDir): TreeEntry
	{
		/**
		 * Structure の構造
		 * 文字列: ファイル名
		 * 配列: キーをディレクトリ名とし、配列または文字列を含む
		 * $structureのキーはかならず `root` になるが、ルートディレクトリ名は `rootDir` を指定する
		 */

		// ツリー構造のルートを設定
		$rootPath = new Path($rootDir);
		$rootDir = Directory::fromPath($rootPath);
		$rootEntry = new TreeEntry($rootDir);

		// ツリー構造を再帰的にパース
		foreach ($structure['root'] as $item) {
			/** @var TreeEntry|TreeNode $node */
			$node = self::parseNode($item, $rootPath);
			$rootEntry->addNode($node);
		}

		return $rootEntry;
	}

	/**
	 * ノードをパースする
	 *
	 * @param mixed $item パース対象の要素（文字列または配列）
	 * @param Path $rootPath ルートディレクトリの `Path` オブジェクト
	 * @return TreeEntry|TreeNode
	 * @throws \InvalidArgumentException パース対象の要素が文字列または配列でない場合
	 */
	public static function parseNode(mixed $item, Path $rootPath): TreeEntry|TreeNode
	{
		// 文字列（ファイル）の場合
		if (\is_string($item)) {
			return TreeNode::fromPath($rootPath->join($item));
		}
		// 配列（ディレクトリ）の場合
		else if (\is_array($item)) {
			$dirName = \array_key_first($item);
			$children = $item[$dirName];

			$dirPath = $rootPath->join($dirName);
			$entry = TreeEntry::fromPath($dirPath);

			foreach ($children as $child) {
				$node = self::parseNode($child, $dirPath);
				$entry->addNode($node);
			}

			return $entry;
		}
		else {
			throw new \InvalidArgumentException('Invalid item format in structure');
		}
	}
}
