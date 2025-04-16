<?php

namespace Tests\PhpScff;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\Application;
use Hytmng\PhpScff\Config\ConfigStorage;

class ApplicationTest extends TestCase
{
	private Application $app;
	private ConfigStorage $configStorage;

	public function setUp(): void
	{
		$this->app = new Application();
		$this->configStorage = new ConfigStorage(sys_get_temp_dir());
	}

	public function testSetConfigStorage()
	{
		$this->app->setConfigStorage($this->configStorage);
		$configStorage = $this->app->getConfigStorage();
		$this->assertInstanceOf(ConfigStorage::class, $configStorage);
	}


}
