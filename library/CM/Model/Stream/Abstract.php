<?php

abstract class CM_Model_Stream_Abstract extends CM_Model_Abstract {

	/**
	 * @param int $timeStamp
	 */
	abstract public function setAllowedUntil($timeStamp);

	abstract public function unsetUser();

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
	public function getChannelId() {
		return (int) $this->_get('channelId');
	}

	/**
	 * @return int
	 */
	public function getStart() {
		return (int) $this->_get('start');
	}

	/**
	 * @return CM_Model_StreamChannel_Abstract
	 */
	public function getStreamChannel() {
		return CM_Model_StreamChannel_Abstract::factory($this->_get('channelId'));
	}

	/**
	 * @return bool
	 */
	public function hasUser() {
		return (null !== $this->getUserId());
	}

	/**
	 * @return CM_Model_User|null
	 */
	public function getUser() {
		if (!$this->hasUser()) {
			return null;
		}
		return CM_Model_User::factory($this->getUserId());
	}

	/**
	 * @return int|null
	 */
	public function getUserId() {
		$userId = $this->_get('userId');
		if (null === $userId) {
			return null;
		}
		return (int) $userId;
	}
}
