<?php

namespace Hytmng\PhpScff\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Parser as YamlParser;
use Hytmng\PhpScff\Application;
use Hytmng\PhpScff\Tree\StructureParser;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\Helper\Msg;

class GroupTreeCommand extends Command
{
	protected function configure(): void
	{
		$this
			->setName('group:tree')
			->setDescription('Render group tree structure like the `tree` command')
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
			throw new InvalidArgumentException('Group ' . Msg::quote($group) . ' is not exists.');
		}

		$group = $configStorage->getGroup($group);
		$metaYamlPath = $group->getMetaYamlPath();

		$yamlParser = new YamlParser();
		$yaml = $yamlParser->parseFile($metaYamlPath->get());
		$structure = $yaml['structure'];

		$rootEntry = StructureParser::parse($structure, 'root');
		$output->writeln($rootEntry);

		return Command::SUCCESS;
	}
}
