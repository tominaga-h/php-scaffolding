<?php

namespace Hytmng\PhpScff;

use Symfony\Component\Console\Application as BaseApplication;
use Hytmng\PhpScff\Config\ConfigStorage;

class Application extends BaseApplication
{
	private ConfigStorage $configStorage;

	/**
	 * 設定フォルダを設定する
	 *
	 * @param ConfigStorage $configStorage
	 */
	public function setConfigStorage(ConfigStorage $configStorage): void
	{
		$this->configStorage = $configStorage;
	}

	/**
	 * 設定フォルダを取得する
	 *
	 * @return ConfigStorage
	 */
	public function getConfigStorage(): ConfigStorage
	{
		return $this->configStorage;
	}

}
