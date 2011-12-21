<?php

abstract class CM_PagingSource_Abstract {
	private $_cacheLifetime;
	private $_cacheLocalLifetime;

	/**
	 * Enable cache
	 * Cost: 2 memcache requests
	 *
	 * @param int $lifetime
	 */
	public function enableCache($lifetime = 600) {
		$this->_cacheLifetime = (int) $lifetime;
	}
	
	/**
	 * Enable local (non-invalidatable) cache
	 * Cost: 1 apc request
	 *
	 * @param int $lifetime
	 */
	public function enableCacheLocal($lifetime = 60) {
		$this->_cacheLocalLifetime = (int) $lifetime;
	}

	/**
	 * Clear cache
	 */
	public function clearCache() {
		if ($this->_cacheLifetime) {
			$tag = CM_Cache::key(CacheConst::PagingSource, $this->_cacheKeyBase());
			CM_Cache::deleteTag($tag);
		}
	}

	protected function _cacheSet($key, $value) {
		if ($this->_cacheLocalLifetime) {
			$key = CM_Cache::key(CacheConst::PagingSource, array($this->_cacheKeyBase(), $key));
			CM_CacheLocal::set($key, $value, $this->_cacheLocalLifetime);
		}
		if ($this->_cacheLifetime) {
			$tag = CM_Cache::key(CacheConst::PagingSource, $this->_cacheKeyBase());
			$key = CM_Cache::key(CacheConst::PagingSource, $key);
			CM_Cache::setTagged($tag, $key, $value, $this->_cacheLifetime);
		}
	}

	protected function _cacheGet($key) {
		if ($this->_cacheLocalLifetime) {
			$key = CM_Cache::key(CacheConst::PagingSource, array($this->_cacheKeyBase(), $key));
			if (($result = CM_CacheLocal::get($key)) !== false) {
				return $result;
			}
		}
		if ($this->_cacheLifetime) {
			$tag = CM_Cache::key(CacheConst::PagingSource, $this->_cacheKeyBase());
			$key = CM_Cache::key(CacheConst::PagingSource, $key);
			if (($result = CM_Cache::getTagged($tag, $key)) !== false) {
				return $result;
			}
		}
		return false;
	}

	/**
	 * @param int $offset
	 * @param int $count
	 * @return int
	 */
	abstract public function getCount($offset = null, $count = null);

	/**
	 * @param int $offset
	 * @param int $count
	 * @return array
	 */
	abstract public function getItems($offset = null, $count = null);

	/**
	 * @return mixed
	 */
	abstract protected function _cacheKeyBase();
}
