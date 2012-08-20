<?php

class CM_Model_StreamChannelArchive_Video extends CM_Model_StreamChannelArchive_Abstract {

	const TYPE = 25;

	/**
	 * @return int
	 */
	public function getCreated() {
		return (int) $this->_get('createStamp');
	}

	/**
	 * @return int
	 */
	public function getDuration() {
		return (int) $this->_get('duration');
	}

	/**
	 * @return string
	 */
	public function getHash() {
		return (string) $this->_get('hash');
	}

	/**
	 * @return int
	 */
	public function getHeight() {
		return (int) $this->_get('height');
	}

	/**
	 * @return int
	 */
	public function getThumbnailCount() {
		return (int) $this->_get('thumbnailCount');
	}

	/**
	 * @return CM_Model_User|null
	 */
	public function getUser() {
		try {
			return CM_Model_User::factory($this->getUserId());
		} catch (CM_Exception_Nonexistent $ex) {
			return null;
		}
	}

	/**
	 * @return int
	 */
	public function getUserId() {
		return (int) $this->_get('userId');
	}

	/**
	 * @return int
	 */
	public function getWidth() {
		return (int) $this->_get('width');
	}

	/**
	 * @return array
	 */
	protected function _loadData() {
		return CM_Mysql::select(TBL_CM_STREAMCHANNELARCHIVE_VIDEO, '*', array('id' => $this->getId()))->fetchAssoc();
	}

	protected static function _create(array $data) {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = $data['streamChannel'];
		$streamPublish = $streamChannel->getStreamPublish();
		$createStamp = $streamPublish->getStart();
		$thumbnailCount = $streamChannel->getThumbnailCount();
		$end = time();
		$duration = $end - $createStamp;
		$hash = md5($streamPublish->getKey());
		CM_Mysql::insert(TBL_CM_STREAMCHANNELARCHIVE_VIDEO, array('id' => $streamChannel->getId(), 'userId' => $streamPublish->getUser()->getId(), 'width' => $streamChannel->getWidth(), 'height' => $streamChannel->getHeight(),
			'duration' => $duration, 'thumbnailCount' => $thumbnailCount, 'hash' => $hash, 'createStamp' => $createStamp));
		return new self($streamChannel->getId());
	}

}