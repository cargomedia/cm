<?php

abstract class CM_Cache_Abstract extends CM_Class_Abstract {

	const RUNTIME_LIFETIME = 3;
	const RUNTIME_CLEAR_INTERVAL = 300;

	/** @var array */
	protected $_runtimeStore = array();

	/** @var int */
	private $_lastClearTimestamp;

	public function __construct() {
		$this->_lastClearTimestamp = time();
	}

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
	 * @param mixed    $value
	 * @param int|null $lifeTime
	 */
	public static final function set($key, $value, $lifeTime = null) {
		if (!static::_enabled()) {
			return;
		}
		$cache = static::getInstance();
		$cache->_setRuntime($key, $value);
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
		if (false !== ($value = $cache->_getRuntime($key))) {
			return $value;
		}
		CM_Debug::get()->incStats(strtolower($cache->_getName()) . '-get', $key);
		if (false !== ($value = $cache->_get($key))) {
			$cache->_setRuntime($key, $value);
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
		$cache->_deleteRuntime($key);
		$cache->_delete($key);
	}

	public static final function flush() {
		if (!static::_enabled()) {
			return;
		}
		$cache = static::getInstance();
		$cache->_flushRuntime();
		$cache->_flush();
	}

	/**
	 * @param string   $tag
	 * @param string   $key
	 * @param mixed    $value
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
	 * @param string      $key
	 * @param mixed       $data
	 * @param int|null    $lifeTime
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
	 * @param string $key
	 * @return mixed|bool
	 */
	private function _getRuntime($key) {
		if (!array_key_exists($key, $this->_runtimeStore)) {
			return false;
		}
		$entry = $this->_runtimeStore[$key];
		if (time() > $entry['expirationStamp']) {
			$this->_deleteRuntime($key);
			return false;
		}
		return $entry['value'];
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	private function _setRuntime($key, $value) {
		$expirationStamp = time() + self::RUNTIME_LIFETIME;
		$this->_runtimeStore[$key] = array('value' => $value, 'expirationStamp' => $expirationStamp);
		if($this->_lastClearTimestamp + self::RUNTIME_CLEAR_INTERVAL < time()) {
			$this->_freeMemory();
		}
	}

	private function _freeMemory() {
		$currentTime = time();
		foreach ($this->_runtimeStore as $key => $data) {
			if ($currentTime > $data['expirationStamp']) {
				$this->_deleteRuntime($key);
			}
		}
		$this->_lastClearTimestamp = time();
	}

	/**
	 * @param string $key
	 */
	private function _deleteRuntime($key) {
		unset($this->_runtimeStore[$key]);
	}

	private function _flushRuntime() {
		$this->_runtimeStore = array();
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
