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
	 * @param string $fileName
	 */
	public static function loadConfig($fileName) {
		foreach (CM_Util::getResourceFiles('config/' . $fileName) as $config) {
			self::load($config->getPath());
		}
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
		self::loadConfig('default.php');
		if (IS_TEST) {
			self::loadConfig('test.php');
		}
		self::loadConfig('local.php');
		self::loadConfig('internal.php');
	}

}
