<?php

class CM_Cache_StorageAdapter_Memcache extends CM_Cache_StorageAdapter_Abstract {

	/** @var CM_Memcache_Client */
	private $_client;

	public function __construct() {
		$this->_client = new CM_Memcache_Client();
	}

	protected function _getName() {
		return 'Memcache';
	}

	protected function _set($key, $value, $lifeTime = null) {
		$this->_client->set($key, $value, $lifeTime);
	}

	protected function _get($key) {
		return $this->_client->get($key);
	}

	protected function _delete($key) {
		$this->_client->delete($key);
	}

	protected function _flush() {
		$this->_client->flush();
	}
}
