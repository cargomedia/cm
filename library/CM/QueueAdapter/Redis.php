<?php

class CM_QueueAdapter_Redis extends CM_QueueAdapter_Abstract {

	/** @var CM_Cache_Redis|null */
	private $_redis = null;

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function push($key, $value) {
		$redis = $this->_getRedisInstance();
		$redis->lPush($this->_getInternalKey($key), $value);
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @param int    $timestamp
	 */
	public function pushDelayed($key, $value, $timestamp) {
		$redis = $this->_getRedisInstance();
		$redis->zAdd($this->_getInternalKey($key), $timestamp, $value);
	}

	/**
	 * @param string $key
	 * @return string|null
	 */
	public function pop($key) {
		$redis = $this->_getRedisInstance();
		return $redis->rPop($this->_getInternalKey($key));
	}

	/**
	 * @param string $key
	 * @param int    $timestampMax
	 * @return array
	 */
	public function popDelayed($key, $timestampMax) {
		$redis = $this->_getRedisInstance();
		$value = $redis->zPopRangeByScore($this->_getInternalKey($key), 0, $timestampMax);
		return $value;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private function _getInternalKey($key) {
		return 'Queue.' . (string) $key;
	}

	/**
	 * @return CM_Cache_Redis
	 */
	private function _getRedisInstance() {
		if (null === $this->_redis) {
			$this->_redis = new CM_Cache_Redis();
		}
		return $this->_redis;
	}
}
