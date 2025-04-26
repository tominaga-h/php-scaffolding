<?php

namespace Hytmng\PhpScff\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Hytmng\PhpScff\Application;

/**
 * List existing group folders.
 */
class ListCommand extends Command
{
	/**
	 * Configure the list command.
	 */
	protected function configure(): void
	{
		$this
			->setName('list')
			->setDescription('List existing group folders');
	}

	/**
	 * Execute the list command.
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int Exit code
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$app = $this->getApplication();
		if (!$app instanceof Application) {
			return Command::FAILURE;
		}
		$configStorage = $app->getConfigStorage();
		$templateDir = $configStorage->getTemplateDir();

		// Gather group folders (non-recursive directories)
		$items = $templateDir->list(false);
		$groups = [];
		foreach ($items as $item) {
			if ($item->isDir()) {
				$groups[] = $item->getPath()->basename();
			}
		}

		if (empty($groups)) {
			$output->writeln('<comment>No group folders found.</comment>');
		} else {
			$output->writeln('<info>Group folders:</info>');
			foreach ($groups as $group) {
				$output->writeln('  - <fg=cyan;options=bold>' . $group . '</>');
			}
		}

		return Command::SUCCESS;
	}
}