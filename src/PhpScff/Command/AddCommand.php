<?php

namespace Hytmng\PhpScff\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Hytmng\PhpScff\Application;
use Hytmng\PhpScff\Template;
use Hytmng\PhpScff\FileSystem\File;
use Hytmng\PhpScff\Exception\ExistenceException;
use Symfony\Component\Console\Input\InputOption;

class AddCommand extends Command
{
	protected function configure(): void
	{
		$this
			->setName('add')
			->setDescription('Add a file as template')
			->addArgument('file', InputArgument::REQUIRED, 'The file path to add')
			->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Group name', null);
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

		$group = $input->getOption('group');
		if ($configStorage->hasTemplate($template, $group)) {
			throw new ExistenceException($this->makeExceptionMsg($filepath, $group));
		}
		$configStorage->addTemplate($template, $group);
		$output->writeln($this->makeSuccessMsg($filepath, $group));

		return Command::SUCCESS;
	}

	private function makeExceptionMsg(string $filepath, ?string $group = null): string
	{
		$message = 'Template already exists: "' . $filepath . '"';
		if ($group !== null) {
			$message .= ' in group "' . $group . '"';
		}
		return $message;
	}

	private function makeSuccessMsg(string $filepath, ?string $group = null): string
	{
		$message = 'Template added: <fg=yellow;options=bold>' . $filepath . '</>';
		if ($group !== null) {
			$message .= ' in group: <fg=blue;options=bold>' . $group . '</>';
		}
		return $message;
	}
}
