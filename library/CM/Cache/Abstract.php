<?php

abstract class CM_Cache_Abstract extends CM_Class_Abstract {

	/** @var CM_Cache_Storage_Abstract */
	protected $_storage;

	/** @var CM_Cache_Storage_Abstract */
	protected $_runtime;

	public function __construct() {
		$storageClassName = static::_getConfig()->storage;
		if (!is_subclass_of($storageClassName, 'CM_Cache_Storage_Abstract')) {
			throw new CM_Exception('Invalid cache storage: `' . $storageClassName . '`');
		}
		$this->_storage = new $storageClassName();
		$this->_runtime = CM_Cache_Storage_Runtime::getInstance();
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
	 * @param string[] $keys
	 * @return mixed[]
	 */
	public final function getMulti(array $keys) {
		$values = $this->_getRuntime()->getMulti($keys);
		if (count($values) === count($keys)) {
			return $values;
		}
		$values = $this->_getStorage()->getMulti($keys);
		foreach ($values as $key => $value) {
			$this->_getRuntime()->set($key, $value);
		}
		return $values;
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
	 * @param mixed  $data
	 * @param int    $lifeTime
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
	 * @return CM_Cache_Storage_Abstract
	 */
	protected function _getRuntime() {
		return $this->_runtime;
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
