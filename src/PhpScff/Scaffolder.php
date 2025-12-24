<?php

namespace Hytmng\PhpScff;

use Symfony\Component\Console\Output\OutputInterface;
use Hytmng\PhpScff\Tree\TreeEntry;
use Hytmng\PhpScff\Tree\TreeNode;
use Hytmng\PhpScff\Service\TwigService;
use Hytmng\PhpScff\Helper\Msg;

class Scaffolder
{
	/**
	 * ツリー構造を再帰的に走査し、ディレクトリ・ファイルを作成する
	 *
	 * @param TreeEntry|TreeNode $node ノード
	 * @param Group $group グループオブジェクト
	 * @param TwigService $twig Twigサービス
	 * @param array<string, mixed> $vars レンダリング変数
	 * @param OutputInterface $output 出力
	 */
	public static function scaffold(
		TreeEntry|TreeNode $node,
		Group $group,
		TwigService $twig,
		array $vars,
		OutputInterface $output
	): void
    {
        $has_error = false;

		if ($node instanceof TreeEntry) {
			// ディレクトリの場合：ディレクトリを作成し、子ノードを再帰処理
			$node->create();
            $output->writeln(Msg::makeLogPrefix(Msg::LOG_CREATED, 'directory: ' . $node->getDirName()));


			foreach ($node->getNodes() as $childNode) {
				self::scaffold($childNode, $group, $twig, $vars, $output);
			}
		} elseif ($node instanceof TreeNode) {
			// ファイルの場合：テンプレートをレンダリングしてファイル作成
			$filename = $node->getFilename();

			// グループ内に対応するテンプレートがあるか確認
			if ($group->hasTemplate($filename)) {
				try {
					$content = $twig->render($filename, $vars);
					$node->create($content);
					$output->writeln(Msg::makeLogPrefix(Msg::LOG_CREATED, 'file: ' . $filename));
				} catch (\Exception $e) {
                    $has_error = true;
					$output->writeln(Msg::makeLogPrefix(Msg::LOG_FAILED, 'file: ' . $filename . ' - ' . $e->getMessage()));
				}
			}
		}

        if ($has_error) {
            throw new \Exception('Failed to scaffold');
        }
	}
}

