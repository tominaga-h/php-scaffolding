<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Hytmng\PhpScff\Config;

class ConfigTest extends TestCase
{
	private Config $config;
	private string $configDir;

	public function setUp(): void
	{
		// 設定フォルダ作成
		$dir = \sys_get_temp_dir();
		$dirname = 'testconfig_' . \uniqid();
		$this->config = new Config($dirname, $dir);
		$this->configDir = $dir . DIRECTORY_SEPARATOR . $dirname;
	}

	public function tearDown(): void
	{
		// 設定フォルダ削除
		$path = \glob($this->config->getConfigDir() . '/*');
		foreach ($path as $p) {
			if (\is_dir($p)) {
				\rmdir($p);
			} else {
				\unlink($p);
			}
		}
		if ($this->config->exists()){
			\rmdir($this->config->getConfigDir());
		}
	}

	public function testConfigDir()
	{
		$actual = $this->config->getConfigDir();
		$expected = $this->configDir;
		$this->assertSame($expected, $actual);
	}

	public function testExists()
	{
		$this->assertFalse($this->config->exists());

		$this->config->create();
		$this->assertTrue($this->config->exists());
	}

	public function testCreate()
	{
		$templateDir = $this->configDir . DIRECTORY_SEPARATOR . 'templates';
		$groupDir = $this->configDir . DIRECTORY_SEPARATOR . 'groups';

		$this->assertFalse(\is_dir($templateDir));
		$this->assertFalse(\is_dir($groupDir));

		$this->config->create();
		$this->assertTrue(\is_dir($templateDir));
		$this->assertTrue(\is_dir($groupDir));

		$this->config->create(); // 既に存在している場合はなにもしない
		$this->assertTrue(\is_dir($templateDir));
		$this->assertTrue(\is_dir($groupDir));
	}
}
