<?php

abstract class CM_VideoStream_Abstract extends CM_Model_Abstract {

	/**
	 * @param int $timeStamp
	 */
	public abstract function setAllowedUntil($timeStamp);

	/**
	 * @return int
	 */
	public function getAllowedUntil() {
		return (int) $this->_get('allowedUntil');
	}

	/**
	 * @return string
	 */
	public function getKey() {
		return (string) $this->_get('key');
	}

	/**
	 * @return int
	 */
	public function getStart() {
		return (int) $this->_get('start');
	}

	/**
	 * @return CM_User
	 */
	public function getUser() {
		return CM_Model_User::factory($this->_get('userId'));
	}
}
