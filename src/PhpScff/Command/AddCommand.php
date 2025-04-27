<?php

namespace Hytmng\PhpScff\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Path as SymfonyPath;
use Hytmng\PhpScff\Application;
use Hytmng\PhpScff\Template;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\Exception\ExistenceException;
use Hytmng\PhpScff\Config\ConfigStorage;
use Hytmng\PhpScff\Helper\Msg;

class AddCommand extends Command
{
	protected function configure(): void
	{
		$this
			->setName('add')
			->setDescription('Add a file as template')
			->addArgument('file', InputArgument::REQUIRED, 'The file path to add')
			->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Group name', ConfigStorage::DEFAULT_GROUP);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$app = $this->getApplication();
		if (!$app instanceof Application) {
			return Command::FAILURE;
		}
		$configStorage = $app->getConfigStorage();

		$filepath = $input->getArgument('file');
		$file = File::fromStringPath($filepath);

		if (!$file->exists()) {
			throw new ExistenceException('File not found: "' . $filepath . '"');
		}

		$template = Template::fromFile($file);
		$absPath = SymfonyPath::makeAbsolute($filepath, \getcwd());

		$group = $input->getOption('group') ?? ConfigStorage::DEFAULT_GROUP;
		if ($configStorage->hasTemplate($template, $group)) {
			throw new ExistenceException($this->makeExceptionMsg($absPath, $group));
		}
		$configStorage->addTemplate($template, $group);
		$output->writeln($this->makeSuccessMsg($absPath, $group));

		return Command::SUCCESS;
	}

	private function makeExceptionMsg(string $filepath, ?string $group = null): string
	{
		$filepath = SymfonyPath::makeAbsolute($filepath, \getcwd());
		return Msg::makeTemplateMsg(Msg::FLG_ALREADY_EXISTS, $filepath, $group);
	}

	private function makeSuccessMsg(string $filepath, ?string $group = null): string
	{
		$filepath = SymfonyPath::makeAbsolute($filepath, \getcwd());
		return Msg::makeTemplateMsg(Msg::FLG_ADDED, $filepath, $group, 'yellow', ['bold'], 'blue', ['bold']);
	}
}
