<?php

abstract class CM_Cache_Apc extends CM_Cache_Abstract {

	protected static $_instance;

	protected function _getName() {
		return 'APC';
	}

	/**
	 * @param string   $key
	 * @param mixed    $data
	 * @param int|null $lifeTime
	 * @return bool
	 */
	protected function _set($key, $data, $lifeTime = null) {
		if (!$lifeTime) {
			$lifeTime = static::_getConfig()->lifetime;
		}
		$key = self::_getKeyArmored($key);
		return apc_store($key, $data, $lifeTime);
	}

	protected function _get($key) {
		$key = self::_getKeyArmored($key);
		return apc_fetch($key);
	}

	/**
	 * @param string[] $keys
	 * @return mixed[]
	 */
	protected  function _getMulti(array $keys) {
		foreach ($keys as &$key) {
			$key = self::_getKeyArmored($key);
		}
		$values = apc_fetch($keys);
		$result = array();
		foreach ($values as $key => $value) {
			$result[$this->_extractKeyArmored($key)] = $value;
		}
		return $result;
	}

	protected function _delete($key) {
		$key = self::_getKeyArmored($key);
		return apc_delete($key);
	}

	protected function _flush() {
		return apc_clear_cache('user');
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
