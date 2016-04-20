<?php

abstract class CM_PagingSource_Abstract {

    /** @var int */
    private $_cacheLifetime;
    private $_cacheLocalLifetime;
    /** @var CM_Cache_Abstract|null */
    private $_cache;

    /**
     * Enable cache
     * Cost: 2 memcache requests
     *
     * @param int               $lifetime
     * @param CM_Cache_Abstract $cache
     */
    public function enableCache($lifetime = 600, CM_Cache_Abstract $cache = null) {
        if (!$cache) {
            $cache = CM_Cache_Shared::getInstance();
        }
        $this->_cache = $cache;
        $this->_cacheLifetime = (int) $lifetime;
    }

    /**
     * Enable local (non-invalidatable) cache
     * Cost: 1 apc request
     *
     * @param int $lifetime
     */
    public function enableCacheLocal($lifetime = 60) {
        $this->enableCache($lifetime, CM_Cache_Local::getInstance());
    }

    /**
     * Clear cache
     */
    public function clearCache() {
        $tag = CM_Cache_Shared::getInstance()->key(CM_CacheConst::PagingSource, $this->_cacheKeyBase());
        if ($this->_cache) {
            $this->_cache->deleteTag($tag);
        }
    }

    /**
     * @return float Chance that an item contains stale (non-processable) data (0-1)
     */
    public function getStalenessChance() {
        return 0;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function _cacheSet($key, $value) {
        if ($this->_cache) {
            $tag = $this->_cache->key(CM_CacheConst::PagingSource, $this->_cacheKeyBase());
            $key = $this->_cache->key(CM_CacheConst::PagingSource, $key);
            $this->_cache->setTagged($tag, $key, $value, $this->_cacheLifetime);
        }
    }

    /**
     * @param string $key
     * @return boolean|mixed
     */
    protected function _cacheGet($key) {
        if ($this->_cache) {
            $tag = $this->_cache->key(CM_CacheConst::PagingSource, $this->_cacheKeyBase());
            $key = $this->_cache->key(CM_CacheConst::PagingSource, $key);
            if (($result = $this->_cache->getTagged($tag, $key)) !== false) {
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
