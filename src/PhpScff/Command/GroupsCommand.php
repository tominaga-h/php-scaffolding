<?php

namespace Hytmng\PhpScff\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Hytmng\PhpScff\Application;
use Hytmng\PhpScff\Template;
use Hytmng\PhpScff\Config\ConfigStorage;

/**
 * List existing group folders.
 */
class GroupsCommand extends Command
{
	protected function configure(): void
	{
		$this
			->setName('groups')
			->setDescription('List all existing groups')
			->addOption('with-templates', 't', InputOption::VALUE_NONE, 'List groups with templates');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$app = $this->getApplication();
		if (!$app instanceof Application) {
			return Command::FAILURE;
		}
		$configStorage = $app->getConfigStorage();
		$WithTemplates = $input->getOption('with-templates');

		if ($WithTemplates) {
			$groups = $configStorage->getTemplatesByGroup();
			$this->outputGroups($output, $groups, true);
		} else {
			$groups = $configStorage->getGroups();
			$this->outputGroups($output, $groups, false);
		}

		return Command::SUCCESS;
	}

	/**
	 * グループ一覧を出力する
	 *
	 * @param OutputInterface $output
	 * @param array          $groups グループ一覧（テンプレート表示時は連想配列、それ以外は通常配列）
	 * @param bool           $showTemplates テンプレートを表示するかどうか
	 */
	private function outputGroups(OutputInterface $output, array $groups, bool $showTemplates): void
	{
		$title = $showTemplates ? 'Templates' : 'Groups';
		$symbol = '├── ';
		$lastSymbol = '└── ';

		if (empty($groups)) {
			$output->writeln("<comment>$title not found.</comment>");
			return;
		}

		$output->writeln("<info>$title:</info>");

		if ($showTemplates) {
			foreach ($groups as $groupName => $templates) {
				$color = $groupName === ConfigStorage::DEFAULT_GROUP ? 'yellow' : 'cyan';
				$output->writeln("  - <fg=$color;options=bold>$groupName</>");
				$count = 0;
				$last = \count($templates) - 1;
				foreach ($templates as $template) {
					if ($template instanceof Template) {
						$filename = $template->getFilename();
						if ($count === $last) {
							$output->writeln("    $lastSymbol $filename");
						} else {
							$output->writeln("    $symbol $filename");
						}
					}
					$count++;
				}
			}
		} else {
			foreach ($groups as $group) {
				$color = $group === ConfigStorage::DEFAULT_GROUP ? 'yellow' : 'cyan';
				$output->writeln("  - <fg=$color;options=bold>$group</>");
			}
		}
	}
}
