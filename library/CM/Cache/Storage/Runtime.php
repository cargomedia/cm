<?php

class CM_Cache_Storage_Runtime extends CM_Cache_Storage_Abstract {

	const LIFETIME_MAX = 3;
	const CLEAR_INTERVAL = 300;

	/** @var int */
	private $_lastClearStamp;

	/** @var CM_Cache_Storage_Runtime */
	private static $_instance;

	/** @var array */
	private $_storage;

	public function __construct() {
		$this->_storage = array();
	}

	public function set($key, $value, $lifetime = null) {
		CM_Debug::getInstance()->incStats(strtolower($this->_getName()) . '-set', $key);
		$this->_set($key, $value, $lifetime);
	}

	public function get($key) {
		CM_Debug::getInstance()->incStats(strtolower($this->_getName()) . '-get', $key);
		return $this->_get($key);
	}

	public function delete($key) {
		$this->_delete($key);
	}

	public function flush() {
		$this->_flush();
	}

	public function getMulti(array $keys) {
		CM_Debug::getInstance()->incStats(strtolower($this->_getName()) . '-getMulti', $keys);
		return $this->_getMulti($keys);
	}

	protected function _getName() {
		return 'Runtime';
	}

	protected function _set($key, $value, $lifetime = null) {
		if (null === $lifetime) {
			$lifetime = self::LIFETIME_MAX;
		} else {
			$lifetime = max($lifetime, self::LIFETIME_MAX);
		}
		$expirationStamp = time() + $lifetime;
		$this->_storage[$key] = array('value' => $value, 'expirationStamp' => $expirationStamp);
		if ($this->_lastClearStamp + self::CLEAR_INTERVAL < time()) {
			$this->_deleteExpired();
		}
	}

	protected function _get($key) {
		if (!array_key_exists($key, $this->_storage) || time() > $this->_storage[$key]['expirationStamp']) {
			return false;
		}
		return $this->_storage[$key]['value'];
	}

	protected function _delete($key) {
		unset($this->_storage[$key]);
	}

	protected function _flush() {
		$this->_storage = array();
	}

	protected function _getRuntime() {
		throw new CM_Exception_Invalid('Cannot use Runtime cache within Runtime');
	}

	private function _deleteExpired() {
		$currentTime = time();
		foreach ($this->_storage as $key => $data) {
			if ($currentTime > $data['expirationStamp']) {
				$this->delete($key);
			}
		}
		$this->_lastClearStamp = $currentTime;
	}

	/**
	 * @return CM_Cache_Storage_Runtime
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
