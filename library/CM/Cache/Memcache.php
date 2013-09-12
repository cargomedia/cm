<?php

abstract class CM_Cache_Memcache extends CM_Cache_Abstract {
	protected static $_instance;
	private $_memcache = null;

	function __construct() {
		$this->_memcache = new Memcache();
		foreach (self::_getConfig()->servers as $server) {
			@$this->_memcache->addServer($server['host'] . ':' . $server['port']);
		}
	}

	protected function _getName() {
		return 'Memcache';
	}

	protected function _set($key, $data, $lifeTime = null) {
		if (!$lifeTime) {
			$lifeTime = self::_getConfig()->lifetime;
		}
		$key = self::_getKeyArmored($key);
		return $this->_memcache->set($key, $data, 0, $lifeTime);
	}

	/**
	 * @param string[] $keys
	 * @return mixed[]
	 */
	public function _getMulti(array $keys) {
		foreach ($keys as &$key) {
			$key = self::_getKeyArmored($key);
		}
		$values = $this->_memcache->get($keys);
		$result = array();
		foreach ($values as $key => $value) {
			$result[$this->_extractKeyArmored($key)] = $value;
		}
		return $result;
	}

	protected function _get($key) {
		$key = self::_getKeyArmored($key);
		return $this->_memcache->get($key);
	}

	protected function _delete($key) {
		$key = self::_getKeyArmored($key);
		return $this->_memcache->delete($key, 0);
	}

	protected function _flush() {
		return $this->_memcache->flush();
	}

	/**
	 * @param string[] $keys
	 * @return mixed[]
	 */
	public static final function getMulti($keys) {
		if (!static::_enabled()) {
			return array();
		}
		/** @var self $cache */
		$cache = self::getInstance();
		CM_Debug::get()->incStats(strtolower($cache->_getName()) . '-getMulti', $keys);
		$value = $cache->_getMulti($keys);
		return $value;
	}
}
