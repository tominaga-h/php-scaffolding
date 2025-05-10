<?php

namespace Hytmng\PhpScff\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Hytmng\PhpScff\Application;
use Hytmng\PhpScff\Helper\Msg;
use Hytmng\PhpScff\Exception\ExistenceException;

class GroupConfigCommand extends Command
{
	protected function configure(): void
	{
		$this
			->setName('group:config')
			->setDescription('Configure group by editing `meta.yaml` file')
			->addArgument('group', InputArgument::REQUIRED, 'Group name');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$app = $this->getApplication();
		if (!$app instanceof Application) {
			return Command::FAILURE;
		}

		$configStorage = $app->getConfigStorage();
		$group = $input->getArgument('group');

		if (!$configStorage->hasGroup($group)) {
			throw new ExistenceException('Group ' . Msg::quote($group) . ' is not exists.');
		}

		$group = $configStorage->getGroup($group);
		$group->editMetaYaml();

		return Command::SUCCESS;
	}
}
