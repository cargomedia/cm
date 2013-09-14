<?php

class CM_Cache_StorageAdapter_Runtime extends CM_Cache_StorageAdapter_Abstract {

	/** @var CM_Cache_StorageAdapter_Runtime */
	private static $_instance;

	/** @var array */
	private $_storage;

	private function __construct() {
		$this->_storage = array();
	}

	protected function _getName() {
		return 'Runtime';
	}

	protected function _set($key, $data, $lifeTime = null) {
		$this->_storage[$key] = $data;
	}

	protected function _get($key) {
		if (!array_key_exists($key, $this->_storage)) {
			return false;
		}
		return $this->_storage[$key];
	}

	protected function _delete($key) {
		unset($this->_storage[$key]);
	}

	protected function _flush() {
		$this->_storage = array();
	}

	/**
	 * @return CM_Cache_StorageAdapter_Runtime
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
