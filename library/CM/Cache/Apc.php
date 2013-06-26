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
			$lifeTime = self::_getConfig()->lifetime;
		}
		$key = self::_getKeyArmored($key);
		return apc_store($key, $data, $lifeTime);
	}

	protected function _get($key) {
		$key = self::_getKeyArmored($key);
		return apc_fetch($key);
	}

	protected function _delete($key) {
		$key = self::_getKeyArmored($key);
		return apc_delete($key);
	}

	protected function _flush() {
		return apc_clear_cache('user');
	}

	protected static function _getConfig() {
		static $config = false;
		if (false === $config) {
			$config = self::_getConfigRaw();
		}
		return $config;
	}
}
