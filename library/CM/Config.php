<?php

class CM_Config {

	/**
	 * @var stdClass
	 */
	private $_config = null;

	private function _init() {
		$this->_config = new stdClass();
		$this->_loadConfig('internal.php');
		$this->_loadConfig('default.php');
		$this->_loadConfig('local.php');
		foreach (CM_Bootloader::getInstance()->getEnvironment() as $environment) {
			$this->_loadConfig($environment . '.php');
		}
	}

	/**
	 * @param string $path
	 */
	private function _load($path) {
		$config = $this->_config;
		require $path;
	}

	/**
	 * @param string $fileName
	 */
	private function _loadConfig($fileName) {
		foreach (CM_Util::getResourceFiles('config/' . $fileName) as $config) {
			$this->_load($config->getPath());
		}
	}

	/**
	 * @return stdClass
	 */
	public static function get() {
		$config = self::_getInstance();
		if (!$config->_config) {
			$config->_init();
		}
		return $config->_config;
	}

	/**
	 * @param stdClass $config
	 */
	public static function set(stdClass $config) {
		self::_getInstance()->_config = $config;
	}

	/**
	 * @return CM_Config
	 */
	private static function _getInstance() {
		return CM_Bootloader::getInstance()->getConfig();
	}
}
