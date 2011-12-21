<?php

/**
 * Abstract cache class for static access on specific implementations.
 * Includes runtime-cache (array) to avoid multiple get()s for the same key.
 * A cache implementation can use a pre-cache (@see _setPreCache()).
 *
 */

abstract class CM_Cache_Abstract {
	protected $_preCache = null;
	protected $_runtimeCache = array();

	/**
	 * @return CM_Cache_Abstract
	 */
	public static final function getInstance() {
		if (!isset(static ::$_instance)) {
			static ::$_instance = new static();
		}
		return static ::$_instance;
	}

	public static final function set($key, $value, $lifeTime = null) {
		if (!static ::_enabled()) {
			return;
		}
		$cache = static::getInstance();
		if ($preCache = $cache->_getPreCache()) {
			$preCache::set($key, $value, $lifeTime);
		}
		$cache->_runtimeCache[$key] = $value;
		CM_Debug::get()->incStats(strtolower($cache->_getName()) . '-set', $key);
		$cache->_set($key, $value, $lifeTime);
	}

	public static final function get($key) {
		if (!static ::_enabled()) {
			return false;
		}
		$cache = static::getInstance();
		if (array_key_exists($key, $cache->_runtimeCache)) {
			return $cache->_runtimeCache[$key];
		}
		if ($preCache = $cache->_getPreCache()) {
			if (($value = $preCache::get($key)) !== false) {
				return $value;
			}
		}
		CM_Debug::get()->incStats(strtolower($cache->_getName()) . '-get', $key);
		if (($value = $cache->_get($key)) !== false) {
			$cache->_runtimeCache[$key] = $value;
			if ($preCache = $cache->_getPreCache()) {
				// Note: Pre-caching uses default life-time!
				$preCache::set($key, $value);
			}
		}
		return $value;
	}

	public static final function delete($key) {

		if (!static ::_enabled()) {
			return;
		}
		$cache = static::getInstance();

		if ($preCache = $cache->_getPreCache()) {
			$preCache::delete($key);
		}
		unset($cache->_runtimeCache[$key]);
		$cache->_delete($key);
	}

	public static final function flush() {
		if (!static ::_enabled()) {
			return;
		}
		$cache = static::getInstance();
		if ($preCache = $cache->_getPreCache()) {
			$preCache::flush();
		}
		$cache->_runtimeCache = array();
		$cache->_flush();
	}

	public static final function setTagged($tag, $key, $value, $lifetime = null) {
		static::_callInstance('setTagged', func_get_args());
	}

	public static final function getTagged($tag, $key) {
		return static::_callInstance('getTagged', func_get_args());
	}

	public static final function deleteTag($tag) {
		static::_callInstance('deleteTag', func_get_args());
	}

	public static final function key($namespace, $key) {
		if (!is_scalar($key)) {
			$key = md5(serialize($key));
		}
		return $namespace . '_' . $key;
	}

	public static final function __callStatic($name, $arguments) {
		static::_callInstance($name, $arguments, true);
	}

	protected static final function _callInstance($functionName, $arguments, $log = false) {
		if (!static::_enabled()) {
			return false;
		}
		$cache = static::getInstance();
		if ($log) {
			CM_Debug::get()->incStats(strtolower($cache->_getName()) . '-' . $functionName, implode(', ', $arguments));
		}
		return call_user_func_array(array($cache, '_' . $functionName), $arguments);
	}

	protected final function _setPreCache(CM_Cache_Abstract $cache) {
		$this->_preCache = $cache;
	}

	protected final function _getPreCache() {
		return $this->_preCache;
	}

	/**
	 * @return boolean
	 */
	protected static function _enabled() {
		return true;
	}

	/**
	 * @return string
	 */
	abstract protected function _getName();

	/**
	 * @param string $key
	 * @param string $data
	 * @param int $lifeTime
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
	 * @param mixed $data
	 * @param int $lifeTime
	 * @return boolean
	 */
	protected final function _setTagged($tag, $key, $data, $lifeTime = null) {
		$key = $key . '_tag:' . $tag . '_tagVersion:' . $this->_getTagVersion($tag);
		return static ::set($key, $data, $lifeTime);
	}

	/**
	 * @param string $tag
	 * @param string $key
	 * @return mixed Result or false
	 */
	protected final function _getTagged($tag, $key) {
		$key = $key . '_tag:' . $tag . '_tagVersion:' . $this->_getTagVersion($tag);
		return static ::get($key);
	}

	/**
	 * @param string $tag
	 * @return boolean
	 */
	protected final function _deleteTag($tag) {
		return static ::delete(CacheConst::Tag_Version . '_tag:' . $tag);
	}

	private final function _getTagVersion($tag) {
		$cacheKey = CacheConst::Tag_Version . '_tag:' . $tag;
		if (($tagVersion = static ::get($cacheKey)) === false) {
			$tagVersion = md5(rand() . uniqid());
			static ::set($cacheKey, $tagVersion);
		}
		return $tagVersion;
	}

}
