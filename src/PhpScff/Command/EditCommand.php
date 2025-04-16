<?php

namespace Hytmng\PhpScff\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Hytmng\PhpScff\Application;
use Hytmng\PhpScff\Exception\ExistenceException;

class EditCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('edit')
            ->setDescription('Edit tempalte for defining parametor')
            ->addArgument('template', Inputargument::REQUIRED, 'The template name to edit')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $app = $this->getApplication();
        if (!$app instanceof Application) {
            return COMMAND::FAILURE;
        }
        $configStorage = $app->getConfigStorage();

        $templateName = $input->getArgument('template');
        if (!$configStorage->hasTemplate($templateName)) {
            throw new ExistenceException('Template "' . $templateName . '" is not found');
        }
        $template = $configStorage->getTemplate($templateName);
        $result = $template->edit();

        if (!$result) {
            $output->writeln('<error>Failed to edit template</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Template edited successfully</info>');
        return Command::SUCCESS;
    }
}
