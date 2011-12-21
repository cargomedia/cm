<?php

abstract class CM_Cache_Memcache extends CM_Cache_Abstract {
	protected static $_instance;
	private $_memcache = null;

	function __construct() {
		$this->_memcache = new Memcache();
		foreach (Config::get()->cache->memcache->servers as $server) {
			@$this->_memcache->addServer($server[0] . ':' . $server[1]);
		}
	}

	protected static function _enabled() {
		return Config::get()->cache->memcache->enabled;
	}

	protected function _getName() {
		return 'Memcache';
	}

	protected function _set($key, $data, $lifeTime = null) {
		if (!$lifeTime) {
			$lifeTime = Config::get()->cache->memcache->lifetime;
		}
		return $this->_memcache->set($key, $data, 0, $lifeTime);
	}

	protected function _get($key) {
		return $this->_memcache->get($key);
	}

	protected function _delete($key) {
		return $this->_memcache->delete($key, 0);
	}

	protected function _flush() {
		return $this->_memcache->flush();
	}

}
