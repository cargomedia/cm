<?php

class CM_Cache_Storage_Apc extends CM_Cache_Storage_Abstract {

	protected function _getName() {
		return 'APC';
	}

	protected function _set($key, $value, $lifeTime = null) {
		$key = $this->_getKeyArmored($key);
		return apc_store($key, $value, $lifeTime);
	}

	protected function _get($key) {
		$key = $this->_getKeyArmored($key);
		return apc_fetch($key);
	}

	protected function _delete($key) {
		$key = $this->_getKeyArmored($key);
		return apc_delete($key);
	}

	protected function _flush() {
		return apc_clear_cache('user');
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private function _getKeyArmored($key) {
		return DIR_ROOT . '_' . $key;
	}
}
