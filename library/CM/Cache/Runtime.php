<?php

class CM_Cache_Runtime extends CM_Class_Abstract {

	const LIFETIME_MAX = 3;
	const CLEAR_INTERVAL = 300;

	/** @var int */
	private $_lastClearStamp;

	/** @var CM_Cache_Runtime */
	private static $_instance;

	/** @var array */
	private $_storage;

	public function __construct() {
		$this->_storage = array();
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param int|null $lifeTime
	 */
	public function set($key, $value, $lifeTime = null) {
		if (null === $lifeTime) {
			$lifeTime = self::LIFETIME_MAX;
		} else {
			$lifeTime = min(self::LIFETIME_MAX, $lifeTime);
		}
		$expirationStamp = time() + $lifeTime;
		$this->_storage[$key] = array('value' => $value, 'expirationStamp' => $expirationStamp);
		if ($this->_lastClearStamp + self::CLEAR_INTERVAL < time()) {
			$this->_deleteExpired();
		}
	}

	/**
	 * @param string $key
	 * @return mixed|false
	 */
	public function get($key) {
		if (!array_key_exists($key, $this->_storage) || time() > $this->_storage[$key]['expirationStamp']) {
			return false;
		}
		return $this->_storage[$key]['value'];
	}

	/**
	 * @param string $key
	 */
	public function delete($key) {
		unset($this->_storage[$key]);
	}

	public function flush() {
		$this->_storage = array();
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
	 * @return CM_Cache_Runtime
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
