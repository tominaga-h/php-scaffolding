<?php

namespace Hytmng\PhpScff\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Hytmng\PhpScff\Application;
use Hytmng\PhpScff\Helper\Msg;
use Hytmng\PhpScff\Config\ConfigStorage;

class GroupRenameCommand extends Command
{
	protected function configure(): void
	{
		$this
			->setName('group:rename')
			->setDescription('Rename a group')
			->addArgument('oldname', InputArgument::REQUIRED, 'Old name of the group')
			->addArgument('newname', InputArgument::REQUIRED, 'New name of the group');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$app = $this->getApplication();
		if (!$app instanceof Application) {
			return Command::FAILURE;
		}

		$configStorage = $app->getConfigStorage();
		$oldname = $input->getArgument('oldname');
		$newname = $input->getArgument('newname');

		if ($oldname === ConfigStorage::DEFAULT_GROUP) {
			throw new InvalidArgumentException('Cannot rename the default group.');
		}

		if ($configStorage->hasGroup($oldname)) {
			$group = $configStorage->getGroup($oldname);
			$group->rename($newname);

			$msg = 'Group renamed: ' . Msg::style($oldname, 'yellow', ['bold']) . ' -> ' . Msg::style($newname, 'blue', ['bold']);
			$output->writeln($msg);
		}

		return Command::SUCCESS;
	}
}
