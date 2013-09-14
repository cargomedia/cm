<?php

abstract class CM_Cache_Abstract extends CM_Class_Abstract {

	/** @var CM_Cache_StorageAdapter_Abstract */
	protected static $_storage;

	/**
	 * @return CM_Cache_StorageAdapter_Abstract
	 */
	public static final function getStorage() {
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
		static::getStorage()->set($key, $value, $lifeTime);
	}

	/**
	 * @param string $key
	 * @return mixed|false
	 */
	public static final function get($key) {
		return static::getStorage()->get($key);
	}

	/**
	 * @param string $key
	 */
	public static final function delete($key) {
		static::getStorage()->delete($key);
	}

	public static final function flush() {
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
	private final function _getTagVersion($tag) {
		$cacheKey = CM_CacheConst::Tag_Version . '_tag:' . $tag;
		if (($tagVersion = static::get($cacheKey)) === false) {
			$tagVersion = md5(rand() . uniqid());
			static::set($cacheKey, $tagVersion);
		}
		return $tagVersion;
	}
}
