<?php

abstract class CM_Cache_Abstract extends CM_Class_Abstract {

	/** @var CM_Cache_Storage_Abstract */
	protected static $_storage;

	/**
	 * @return CM_Cache_Storage_Abstract
	 */
	public static function getStorage() {
		if (!isset(static::$_storage)) {
			$storageClassName = static::_getConfig()->storageAdapter;
			static::$_storage = new $storageClassName();
		}
		return static::$_storage;
	}

	/**
	 * @param string   $key
	 * @param mixed    $value
	 * @param int|null $lifeTime
	 */
	public static final function set($key, $value, $lifeTime = null) {
		if (!$lifeTime) {
			$lifeTime = static::_getConfig()->lifetime;
		}
		static::getStorage()->set($key, $value, $lifeTime);
		static::_getRuntimeStorage()->set($key, $value, $lifeTime);
	}

	/**
	 * @param string $key
	 * @return mixed|false
	 */
	public static final function get($key) {
		$runtimeStorage = static::_getRuntimeStorage();
		if (false !== ($value = $runtimeStorage->get($key))) {
			return $value;
		}
		if (false !== ($value = static::getStorage()->get($key))) {
			$runtimeStorage->set($key, $value);
		}
		return $value;
	}

	/**
	 * @param string $key
	 */
	public static final function delete($key) {
		static::_getRuntimeStorage()->delete($key);
		static::getStorage()->delete($key);
	}

	public static final function flush() {
		static::_getRuntimeStorage()->flush();
		static::getStorage()->flush();
	}

	/**
	 * @param string $tag
	 * @param string $key
	 * @param mixed  $data
	 * @param int    $lifeTime
	 * @return boolean
	 */
	public static final function setTagged($tag, $key, $data, $lifeTime = null) {
		$key = $key . '_tag:' . $tag . '_tagVersion:' . static::_getTagVersion($tag);
		static::set($key, $data, $lifeTime);
	}

	/**
	 * @param string $tag
	 * @param string $key
	 * @return mixed Result or false
	 */
	public static final function getTagged($tag, $key) {
		$key = $key . '_tag:' . $tag . '_tagVersion:' . static::_getTagVersion($tag);
		return static::get($key);
	}

	/**
	 * @param string $tag
	 * @return boolean
	 */
	public static final function deleteTag($tag) {
		static::delete(CM_CacheConst::Tag_Version . '_tag:' . $tag);
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
	 * @param string $tag
	 * @return string
	 */
	private static final function _getTagVersion($tag) {
		$cacheKey = CM_CacheConst::Tag_Version . '_tag:' . $tag;
		if (($tagVersion = static::get($cacheKey)) === false) {
			$tagVersion = md5(rand() . uniqid());
			static::set($cacheKey, $tagVersion);
		}
		return $tagVersion;
	}

	/**
	 * @return CM_Cache_Storage_Runtime
	 */
	private static final function _getRuntimeStorage() {
		return CM_Cache_Storage_Runtime::getInstance();
	}
}
