<?php

namespace Hytmng\PhpScff\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Hytmng\PhpScff\Application;
use Hytmng\PhpScff\Exception\ExistenceException;
use Hytmng\PhpScff\Config\ConfigStorage;
use Hytmng\PhpScff\Helper\Msg;

class TemplateEditCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('template:edit')
            ->setDescription('Edit template for defining parameter')
            ->addArgument('template', InputArgument::REQUIRED, 'The template name to edit')
            ->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Group name', ConfigStorage::DEFAULT_GROUP)
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
        $group = $input->getOption('group') ?? ConfigStorage::DEFAULT_GROUP;

        // テンプレートが存在しない場合はエラー
        if (!$configStorage->hasTemplate($templateName, $group)) {
            $msg = Msg::makeTemplateMsg(Msg::FLG_NOT_FOUND, $templateName, $group);
            $msg .= "\nYou can check templates using `group:list -f` command.";
            throw new ExistenceException($msg);
        }

        $template = $configStorage->getTemplate($templateName, $group);
        $result = $template->edit();

        if (!$result) {
            $output->writeln('<error>Failed to edit template</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Template edited successfully</info>');
        return Command::SUCCESS;
    }
}
