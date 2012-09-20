<?php

class CM_Lock extends CM_Class_Abstract {

	/**
	 * @param string $name
	 */
	public function __construct($name) {
		$this->_key = CM_CacheConst::Lock. '_lock:' . $name;
	}

	/**
	 * @param int|null $interval
	 */
	public function waitUntilUnlocked($interval = null) {
		if (!$interval) {
			$interval = 0.1;
		}
		while ($this->_isLocked()) {
			usleep($interval * 1000000);
		}
	}

	/**
	 * @param int|null $lifeTime
	 */
	public function lock($lifeTime = null) {
		if (!$lifeTime) {
			$lifeTime = 600;
		}
		CM_CacheLocal::set($this->_key, time() + $lifeTime, $lifeTime);
	}

	public function unlock() {
		CM_CacheLocal::set($this->_key, false);
	}

	/**
	 * @return bool
	 */
	private function _isLocked() {
		$lockTime = CM_CacheLocal::get($this->_key) ;
		if (false === $lockTime) {
			return false;
		}
		return $lockTime > time();
	}

}
