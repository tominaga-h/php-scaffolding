<?php

namespace Hytmng\PhpScff;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Hytmng\PhpScff\Application;
use Hytmng\PhpScff\Config\ConfigStorage;
use Hytmng\PhpScff\FileSystem\Path;

class Kernel
{
	private ContainerBuilder $container;
	private Application $application;
	private ConfigStorage $configStorage;

	public function __construct()
	{
		$this->container = new ContainerBuilder();
		$path = Path::from(__DIR__, '/../../config');
		$loader = new YamlFileLoader($this->container, new FileLocator($path->get()));
		$loader->load('services.yaml');
		$this->container->compile();
	}

	public function getContainer(): ContainerBuilder
	{
		return $this->container;
	}

	public function setConfigStorage(ConfigStorage $configStorage): void
	{
		$this->configStorage = $configStorage;
	}

	public function getConfigStorage(): ConfigStorage
	{
		return $this->configStorage;
	}

	public function setApplication(Application $application): void
	{
		$this->application = $application;
	}

	public function getApplication(): Application
	{
		return $this->application;
	}

	public function getCommands(): array
	{
		$services = $this->container->findTaggedServiceIds('console.command');
		$commands = [];
		foreach ($services as $id => $_) {
			$commands[] = $this->container->get($id);
		}
		return $commands;
	}

	public function run(): int
	{
		// 設定フォルダの作成
		if (!$this->configStorage->exists()) {
			$this->configStorage->create();
		}

		// コマンドの登録
		$commands = $this->getCommands();
		$this->application->addCommands($commands);

		// ConfigStorageの設定
		$this->application->setConfigStorage($this->configStorage);

		return $this->application->run();
	}
}
