<?php

class CM_QueueAdapter_Redis extends CM_QueueAdapter_Abstract {

	public function push($key, $value) {
		CM_Cache_Redis::lPush($key, $value);
	}

	public function pop($key) {
		return CM_Cache_Redis::rPop($key);
	}
}
