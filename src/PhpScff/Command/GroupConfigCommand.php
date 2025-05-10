<?php

namespace Hytmng\PhpScff\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Parser as YamlParser;
use Hytmng\PhpScff\Application;
use Hytmng\PhpScff\FileSystem\Path;
use Hytmng\PhpScff\Tree\StructureParser;

class GroupConfigCommand extends Command
{
	protected function configure(): void
	{
		$this
			->setName('group:config')
			->setDescription('Configure group by yaml file')
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

		$yamlFilePath = Path::from(__DIR__, '/../../../config/test.yaml');
		$yamlParser = new YamlParser();
		$yaml = $yamlParser->parseFile($yamlFilePath->get());
		$structure = $yaml['structure'];

		$rootEntry = StructureParser::parse($structure, 'root');
		$output->writeln('ツリー構造:');
		$output->writeln($rootEntry);

		return Command::SUCCESS;
	}
}
