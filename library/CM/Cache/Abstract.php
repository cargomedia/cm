<?php

abstract class CM_Cache_Abstract extends CM_Class_Abstract {

	/** @var CM_Cache_Storage_Abstract */
	protected $_storage;


	public function __construct() {
		$storageClassName = static::_getConfig()->storage;
		$this->_storage = new $storageClassName();
	}

	/**
	 * @param string   $key
	 * @param mixed    $value
	 * @param int|null $lifeTime
	 */
	public final function set($key, $value, $lifeTime = null) {
		if (!$lifeTime) {
			$lifeTime = static::_getConfig()->lifetime;
		}
		$this->_getStorage()->set($key, $value, $lifeTime);
		$this->_getRuntime()->set($key, $value, $lifeTime);
	}

	/**
	 * @param string $key
	 * @return mixed|false
	 */
	public final function get($key) {
		if (false !== ($value = $this->_getRuntime()->get($key))) {
			return $value;
		}
		if (false !== ($value = $this->_getStorage()->get($key))) {
			$this->_getRuntime()->set($key, $value);
		}
		return $value;
	}

	/**
	 * @param string $key
	 */
	public final function delete($key) {
		$this->_getRuntime()->delete($key);
		$this->_getStorage()->delete($key);
	}

	public final function flush() {
		$this->_getRuntime()->flush();
		$this->_getStorage()->flush();
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
	 * @return mixed
	 */
	public static final function __callStatic($name, $arguments) {
		return static::_callInstance($name, $arguments, true);
	}

	/**
	 * @param string    $functionName
	 * @param array     $arguments
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
	 * @param string $keyArmored
	 * @return string mixed
	 * @throws CM_Exception_Invalid
	 */
	protected static final function _extractKeyArmored($keyArmored) {
		if (!preg_match('/^' . preg_quote(DIR_ROOT, '/') . '_' . '(.+)$/', $keyArmored, $matches)) {
			throw new CM_Exception_Invalid('Cannot extract key from `' . $keyArmored . '`');
		}
		return $matches[1];
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
	 * @param string   $key
	 * @param mixed    $data
	 * @param int|null $lifeTime
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
	 * @param int    $lifeTime
	 * @return boolean
	 */
	public final function setTagged($tag, $key, $data, $lifeTime = null) {
		$key = $key . '_tag:' . $tag . '_tagVersion:' . $this->_getTagVersion($tag);
		$this->set($key, $data, $lifeTime);
	}

	/**
	 * @param string $tag
	 * @param string $key
	 * @return mixed Result or false
	 */
	public final function getTagged($tag, $key) {
		$key = $key . '_tag:' . $tag . '_tagVersion:' . $this->_getTagVersion($tag);
		return $this->get($key);
	}

	/**
	 * @param string $tag
	 * @return boolean
	 */
	public final function deleteTag($tag) {
		$this->delete(CM_CacheConst::Tag_Version . '_tag:' . $tag);
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
	 * @return CM_Cache_Storage_Abstract
	 */
	protected function _getStorage() {
		return $this->_storage;
	}

	/**
	 * @return CM_Cache_Storage_Runtime
	 */
	protected function _getRuntime() {
		return CM_Cache_Storage_Runtime::getInstance();
	}

	/**
	 * @param string $tag
	 * @return string
	 */
	private final function _getTagVersion($tag) {
		$cacheKey = CM_CacheConst::Tag_Version . '_tag:' . $tag;
		if (($tagVersion = $this->get($cacheKey)) === false) {
			$tagVersion = md5(rand() . uniqid());
			$this->set($cacheKey, $tagVersion);
		}
		return $tagVersion;
	}
}
