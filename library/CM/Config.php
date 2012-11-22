<?php

class CM_Config {

	/**
	 * @var stdClass
	 */
	private static $_config = null;

	/**
	 * @param string $path
	 */
	public static function load($path) {
		$config = self::get();
		require $path;
	}

	/**
	 * @return stdClass
	 */
	public static function get() {
		if (!self::$_config) {
			self::_init();
		}
		return self::$_config;
	}

	/**
	 * @param stdClass $config
	 */
	public static function set($config) {
		self::$_config = $config;
	}

	private static function _init() {
		self::$_config = new stdClass();
		self::_loadConfig('default.php');
		if (IS_TEST) {
			self::_loadConfig('test.php');
		}
		self::_loadConfig('local.php');
		self::_loadConfig('internal.php');
	}

	/**
	 * @param string $fileName
	 */
	private static function _loadConfig($fileName) {
		$applicationConfigPath = DIR_ROOT . 'resources/config/';

		foreach (CM_Util::getNamespaceFiles('resources/config/' . $fileName) as $config) {
			self::load($config->getPath());
		}
		if (is_file($applicationConfigPath . $fileName)) {
			self::load($applicationConfigPath . $fileName);
		}
	}
}
