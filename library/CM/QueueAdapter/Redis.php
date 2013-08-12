<?php

class CM_QueueAdapter_Redis extends CM_QueueAdapter_Abstract {

	public function push($key, $value) {
		CM_Cache_Redis::lPush($key, $value);
	}

	public function pushDelayed($key, $value, $timestamp) {
		CM_Cache_Redis::zAdd($key, $timestamp, $value);
	}

	public function pop($key) {
		return CM_Cache_Redis::rPop($key);
	}

	public function popDelayed($key, $timestampMax) {
		$value = CM_Cache_Redis::zRangeByScore($key, 0, $timestampMax, 1);
		$value = reset($value);
		CM_Cache_Redis::zRem($key, $value);
		return $value;
	}
}
