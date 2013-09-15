<?php

abstract class CM_Cache_Storage_Abstract extends CM_Class_Abstract {

	/**
	 * @param string   $key
	 * @param mixed    $value
	 * @param int|null $lifeTime
	 */
	public final function set($key, $value, $lifeTime = null) {
		CM_Debug::get()->incStats(strtolower($this->_getName()) . '-set', $key);
		$key = $this->_getKeyArmored($key);
		$this->_set($key, $value, $lifeTime);
	}

	/**
	 * @param string $key
	 * @return mixed|false
	 */
	public final function get($key) {
		CM_Debug::get()->incStats(strtolower($this->_getName()) . '-get', $key);
		$key = $this->_getKeyArmored($key);
		return $this->_get($key);
	}

	/**
	 * @param string $key
	 */
	public final function delete($key) {
		$key = $this->_getKeyArmored($key);
		$this->_delete($key);
	}

	public final function flush() {
		$this->_flush();
	}

	/**
	 * @param mixed $keyPart ...
	 * @return string
	 */
	public final function key($keyPart) {
		$parts = func_get_args();
		foreach ($parts as &$part) {
			if (!is_scalar($part)) {
				$part = md5(serialize($part));
			}
		}
		return implode('_', $parts);
	}

	/**
	 * @return string
	 */
	abstract protected function _getName();

	/**
	 * @param string   $key
	 * @param mixed    $value
	 * @param int|null $lifeTime
	 * @return boolean
	 */
	abstract protected function _set($key, $value, $lifeTime = null);

	/**
	 * @param string $key
	 * @return mixed Result or false
	 */
	abstract protected function _get($key);

	/**
	 * @param string $key
	 * @return boolean
	 */
	abstract protected function _delete($key);

	/**
	 * @return boolean
	 */
	abstract protected function _flush();

	/**
	 * @param string $key
	 * @return string
	 */
	protected function _getKeyArmored($key) {
		return DIR_ROOT . '_' . $key;
	}
}
