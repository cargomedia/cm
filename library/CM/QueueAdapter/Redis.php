<?php

class CM_QueueAdapter_Redis extends CM_QueueAdapter_Abstract {

    /** @var CM_Redis_Client|null */
    private $_redisClient = null;

    public function __construct() {
        $this->_redisClient = CM_Service_Manager::getInstance()->getRedis();
    }

    public function push($key, $value) {
        $redis = $this->_getRedisClient();
        $redis->lPush($this->_getInternalKey($key), $value);
    }

    public function pushDelayed($key, $value, $timestamp) {
        $redis = $this->_getRedisClient();
        $redis->zAdd($this->_getInternalKey($key), $timestamp, $value);
    }

    public function pop($key) {
        $redis = $this->_getRedisClient();
        return $redis->rPop($this->_getInternalKey($key));
    }

    public function popDelayed($key, $timestampMax) {
        $redis = $this->_getRedisClient();
        $value = $redis->zPopRangeByScore($this->_getInternalKey($key), 0, $timestampMax);
        return $value;
    }

    /**
     * Updates the time to live of the whole queue, not of single entries
     *
     * @param string $key
     * @param int    $ttl
     */
    public function setTtl($key, $ttl) {
        $redis = $this->_getRedisClient();
        $redis->expire($this->_getInternalKey($key), $ttl);
    }

    /**
     * @param string $key
     * @return string
     */
    private function _getInternalKey($key) {
        return 'Queue.' . (string) $key;
    }

    /**
     * @return CM_Redis_Client
     */
    private function _getRedisClient() {
        return $this->_redisClient;
    }
}
