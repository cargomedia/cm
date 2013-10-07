<?php

class CM_Cache_Storage_Runtime extends CM_Cache_Storage_Abstract {

	const RUNTIME_LIFETIME = 3;
	const RUNTIME_CLEAR_INTERVAL = 300;

	/** @var int */
	private $_lastClearStamp;

	/** @var CM_Cache_Storage_Runtime */
	private static $_instance;

	/** @var array */
	private $_storage;

	public function __construct() {
		$this->_storage = array();
	}

	protected function _getName() {
		return 'Runtime';
	}

	protected function _set($key, $value, $lifeTime = null) {
		$expirationStamp = time() + self::RUNTIME_LIFETIME;
		$this->_storage[$key] = array('value' => $value, 'expirationStamp' => $expirationStamp);
		if ($this->_lastClearStamp + self::RUNTIME_CLEAR_INTERVAL < time()) {
			$this->_deleteExpired();
		}
		$this->_storage[$key] = $value;
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

	private function _deleteExpired() {
		$currentTime = time();
		foreach ($this->_storage as $key => $data) {
			if ($currentTime > $data['expirationStamp']) {
				$this->_delete($key);
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
