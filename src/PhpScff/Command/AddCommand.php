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

class AddCommand extends Command
{
	protected function configure(): void
	{
		$this
			->setName('add')
			->setDescription('Add a file as template')
			->addArgument('file', InputArgument::REQUIRED, 'The file path to add');
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

		if ($configStorage->hasTemplate($template)) {
			throw new ExistenceException('Template already exists: "' . $filepath . '"');
		}

		$configStorage->addTemplate($template);
		$output->writeln('Template added: <fg=yellow;options=bold>"' . $filepath . '"</>');

		return Command::SUCCESS;
	}
}
