<?php

class CM_QueueAdapter_Redis extends CM_QueueAdapter_Abstract {


	public function push($key, $value) {
		CM_Cache_Redis::lPush($this->_getInternalKey($key), $value);
	}

	public function pushDelayed($key, $value, $timestamp) {
		CM_Cache_Redis::zAdd($this->_getInternalKey($key), $timestamp, $value);
	}

	public function pop($key) {
		return CM_Cache_Redis::rPop($this->_getInternalKey($key));
	}

	public function popDelayed($key, $timestampMax) {
		$value = CM_Cache_Redis::zRangeByScore($this->_getInternalKey($key), 0, $timestampMax, 1);
		$value = reset($value);
		CM_Cache_Redis::zRem($this->_getInternalKey($key), $value);
		return $value;
	}

	private function _getInternalKey($key) {
		return 'Queue.' . (string) $key;
	}
}
