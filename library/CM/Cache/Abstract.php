<?php

abstract class CM_Cache_Abstract extends CM_Class_Abstract {
	protected $_runtimeCache = array();

	/**
	 * @return CM_Cache_Abstract
	 */
	public static final function getInstance() {
		if (!isset(static::$_instance)) {
			static::$_instance = new static();
		}
		return static::$_instance;
	}

	/**
	 * @param string   $key
	 * @param mixed	$value
	 * @param int|null $lifeTime
	 */
	public static final function set($key, $value, $lifeTime = null) {
		if (!static::_enabled()) {
			return;
		}
		$cache = static::getInstance();
		$cache->_runtimeCache[$key] = $value;
		CM_Debug::get()->incStats(strtolower($cache->_getName()) . '-set', $key);
		$cache->_set($key, $value, $lifeTime);
	}

	/**
	 * @param string $key
	 * @return mixed|false
	 */
	public static final function get($key) {
		if (!static::_enabled()) {
			return false;
		}
		$cache = static::getInstance();
		if (array_key_exists($key, $cache->_runtimeCache)) {
			return $cache->_runtimeCache[$key];
		}
		CM_Debug::get()->incStats(strtolower($cache->_getName()) . '-get', $key);
		if (($value = $cache->_get($key)) !== false) {
			$cache->_runtimeCache[$key] = $value;
		}
		return $value;
	}

	/**
	 * @param string $key
	 */
	public static final function delete($key) {
		if (!static::_enabled()) {
			return;
		}
		$cache = static::getInstance();
		unset($cache->_runtimeCache[$key]);
		$cache->_delete($key);
	}

	public static final function flush() {
		if (!static::_enabled()) {
			return;
		}
		$cache = static::getInstance();
		$cache->_runtimeCache = array();
		$cache->_flush();
	}

	/**
	 * @param string   $tag
	 * @param string   $key
	 * @param mixed	$value
	 * @param int|null $lifetime
	 */
	public static final function setTagged($tag, $key, $value, $lifetime = null) {
		static::_callInstance('setTagged', func_get_args());
	}

	/**
	 * @param string $tag
	 * @param string $key
	 * @return mixed|false
	 */
	public static final function getTagged($tag, $key) {
		return static::_callInstance('getTagged', func_get_args());
	}

	/**
	 * @param string $tag
	 */
	public static final function deleteTag($tag) {
		static::_callInstance('deleteTag', func_get_args());
	}

	/**
	 * @param mixed $keyPart ...
	 * @return string
	 */
	public static final function key($keyPart) {
		$parts = func_get_args();
		foreach ($parts as &$part) {
			if (!is_scalar($part)) {
				$part = md5(serialize($part));
			}
		}
		return implode('_', $parts);
	}

	/**
	 * @param string $name
	 * @param array  $arguments
	 */
	public static final function __callStatic($name, $arguments) {
		return static::_callInstance($name, $arguments, true);
	}

	/**
	 * @param string	$functionName
	 * @param array	 $arguments
	 * @param bool|null $log
	 * @return mixed
	 */
	protected static final function _callInstance($functionName, $arguments, $log = null) {
		if (!static::_enabled()) {
			return false;
		}
		if (is_null($log)) {
			$log = false;
		}
		$cache = static::getInstance();
		if ($log) {
			CM_Debug::get()->incStats(strtolower($cache->_getName()) . '-' . $functionName, implode(', ', $arguments));
		}
		return call_user_func_array(array($cache, '_' . $functionName), $arguments);
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected static final function _getKeyArmored($key) {
		return DIR_ROOT . '_' . $key;
	}

	/**
	 * @return boolean
	 */
	protected static function _enabled() {
		return static::_getConfig()->enabled;
	}

	/**
	 * @return string
	 */
	abstract protected function _getName();

	/**
	 * @param string	  $key
	 * @param mixed	   $data
	 * @param int|null	$lifeTime
	 * @return boolean
	 */
	abstract protected function _set($key, $data, $lifeTime = null);

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
	 * @param string $tag
	 * @param string $key
	 * @param mixed  $data
	 * @param int	$lifeTime
	 * @return boolean
	 */
	protected final function _setTagged($tag, $key, $data, $lifeTime = null) {
		$key = $key . '_tag:' . $tag . '_tagVersion:' . $this->_getTagVersion($tag);
		return static::set($key, $data, $lifeTime);
	}

	/**
	 * @param string $tag
	 * @param string $key
	 * @return mixed Result or false
	 */
	protected final function _getTagged($tag, $key) {
		$key = $key . '_tag:' . $tag . '_tagVersion:' . $this->_getTagVersion($tag);
		return static::get($key);
	}

	/**
	 * @param string $tag
	 * @return boolean
	 */
	protected final function _deleteTag($tag) {
		return static::delete(CM_CacheConst::Tag_Version . '_tag:' . $tag);
	}

	/**
	 * @param string $tag
	 * @return string
	 */
	private final function _getTagVersion($tag) {
		$cacheKey = CM_CacheConst::Tag_Version . '_tag:' . $tag;
		if (($tagVersion = static::get($cacheKey)) === false) {
			$tagVersion = md5(rand() . uniqid());
			static::set($cacheKey, $tagVersion);
		}
		return $tagVersion;
	}

}
