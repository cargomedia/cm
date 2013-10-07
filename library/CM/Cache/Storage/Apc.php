<?php

class CM_Cache_Storage_Apc extends CM_Cache_Storage_Abstract {

	protected function _getName() {
		return 'APC';
	}

	protected function _set($key, $value, $lifeTime = null) {
		return apc_store($key, $value, $lifeTime);
	}

	protected function _get($key) {
		return apc_fetch($key);
	}

	protected function _delete($key) {
		return apc_delete($key);
	}

	protected function _flush() {
		return apc_clear_cache('user');
	}

	protected function _getMulti(array $keys) {
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
}
