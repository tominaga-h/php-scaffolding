<?php

namespace Hytmng\PhpScff\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Hytmng\PhpScff\Application;
use Hytmng\PhpScff\Scaffolder;
use Hytmng\PhpScff\Tree\StructureParser;
use Hytmng\PhpScff\Service\TwigService;
use Hytmng\PhpScff\Exception\ExistenceException;
use Hytmng\PhpScff\Helper\Msg;
use Hytmng\PhpScff\Helper\YamlParser;

class NewCommand extends Command
{
	protected function configure(): void
	{
		$this
			->setName('new')
			->setDescription('Create a new project by scaffolding')
			->addArgument('group', InputArgument::REQUIRED, 'Group name')
			->addArgument('directory', InputArgument::OPTIONAL, 'Directory name', '.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$app = $this->getApplication();
		if (!$app instanceof Application) {
			return Command::FAILURE;
		}

		$configStorage = $app->getConfigStorage();
		$groupName = $input->getArgument('group');
		$directory = $input->getArgument('directory');

		// グループの存在確認
		if (!$configStorage->hasGroup($groupName)) {
			throw new ExistenceException('Group ' . Msg::quote($groupName) . ' does not exist.');
		}

		// グループオブジェクトを取得
		$group = $configStorage->getGroup($groupName);

		// meta.yamlからstructureを読み込み
		$metaYamlPath = $group->getMetaYamlPath();
		$yaml = YamlParser::parseFile($metaYamlPath->get());
		$structure = $yaml['structure'];

		// ターゲットディレクトリをルートとしてツリー構造を構築
		$rootEntry = StructureParser::parse($structure, $directory);

		// TwigServiceを準備（グループディレクトリをテンプレートディレクトリに設定）
		$twig = new TwigService();
		$twig->setTemplateDir($metaYamlPath->dirname());

		// レンダリング変数
		$vars = [
			'groupName' => $groupName,
			'directory' => basename($directory),
		];

		$output->writeln('Scaffolding started at: <info>' . (realpath($directory) ?: $directory) . '</info>');

		try {
		    // スカフォールディング実行
			Scaffolder::scaffold($rootEntry, $group, $twig, $vars, $output);
		} catch (\Exception $e) {
            // 対象ディレクトリを削除
            $rootEntry->remove();
            $output->writeln(Msg::makeLogPrefix(Msg::LOG_DELETED, 'directory: ' . $rootEntry->getDirName()));

			$output->writeln(Msg::makeLogPrefix(Msg::LOG_ERROR, $e->getMessage()));
			return Command::FAILURE;
		}

		$output->writeln('Scaffolding completed.');

		return Command::SUCCESS;
	}
}
