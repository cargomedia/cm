<?php

class CM_Config {

	/**
	 * @var stdClass
	 */
	private static $_config = null;

	/**
	 * @param string $filename
	 */
	public static function load($filename) {
		$path = DIR_ROOT . 'config' . DIRECTORY_SEPARATOR . $filename;
		if (is_file($path)) {
			$config = self::get();
			require $path;
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
		self::load('cm.php');
		self::load('default.php');
		if (IS_TEST) {
			self::load('test.php');
		}
		self::load('local.php');
		self::load('class-types.php');
	}
}
