<?php

abstract class CM_Cache_Storage_Abstract extends CM_Class_Abstract {

	/**
	 * @param string   $key
	 * @param mixed    $value
	 * @param int|null $lifeTime
	 */
	public final function set($key, $value, $lifeTime = null) {
		CM_Debug::getInstance()->incStats(strtolower($this->_getName()) . '-set', $key);
		$key = $this->_getKeyArmored($key);
		$this->_set($key, $value, $lifeTime);
	}

	/**
	 * @param string $key
	 * @return mixed|false
	 */
	public final function get($key) {
		CM_Debug::getInstance()->incStats(strtolower($this->_getName()) . '-get', $key);
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
	 * @param string[] $keys
	 * @return mixed[]
	 */
	public final function getMulti(array $keys) {
		CM_Debug::getInstance()->incStats(strtolower($this->_getName()) . '-getMulti', $keys);
		foreach ($keys as &$key) {
			$key = self::_getKeyArmored($key);
		}
		$values = $this->_getMulti($keys);
		$result = array();
		foreach ($values as $armoredKey => $value) {
			$result[$this->_extractKeyArmored($armoredKey)] = $value;
		}
		return $result;
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
	 * @param string[] $keys
	 * @return mixed[]
	 */
	protected function _getMulti(array $keys) {
		$values = array();
		foreach ($keys as $key) {
			$value = $this->_get($key);
			if (false !== $value) {
				$values[$key] = $value;
			}
		}
		return $values;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected function _getKeyArmored($key) {
		return CM_Bootloader::getInstance()->getDataPrefix() . DIR_ROOT . '_' . $key;
	}

	/**
	 * @param string $keyArmored
	 * @return string mixed
	 * @throws CM_Exception_Invalid
	 */
	protected static final function _extractKeyArmored($keyArmored) {
		$prefix = CM_Bootloader::getInstance()->getDataPrefix() . DIR_ROOT;
		if (!preg_match('/^' . preg_quote($prefix, '/') . '_' . '(.+)$/', $keyArmored, $matches)) {
			throw new CM_Exception_Invalid('Cannot extract key from `' . $keyArmored . '`');
		}
		return $matches[1];
	}
}
