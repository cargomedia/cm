<?php

class CM_Config {

	/**
	 * @var stdClass
	 */
	private static $_config = null;

	/**
	 * @var string Path to config folder
	 */
	private static $_configPath = '';

	/**
	 * @param string $file Config file name to load
	 */
	public static function load($file) {
		$filePath = self::$_configPath . $file;
		if (file_exists($filePath)) {
			include $filePath;
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

	private static function _init() {
		self::$_config = new stdClass();
		self::$_configPath = DIR_ROOT . 'config' . DIRECTORY_SEPARATOR;
		self::load('default.php');
		self::load('local.php');
	}
}
