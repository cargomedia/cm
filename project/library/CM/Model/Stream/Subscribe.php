<?php

class CM_Model_Stream_Subscribe extends CM_Model_Stream_Abstract {

	const TYPE = 22;

	public function setAllowedUntil($timeStamp) {
		CM_Mysql::update(TBL_CM_STREAM_SUBSCRIBE, array('allowedUntil' => (int) $timeStamp), array('id' => $this->getId()));
		$this->_change();
	}

	protected function _getContainingCacheables() {
		$cacheables = parent::_getContainingCacheables();
		$cacheables[] = $this->getStreamChannel()->getStreamSubscribes();
		$cacheables[] = $this->getStreamChannel()->getSubscribers();
		return $cacheables;
	}

	protected function _loadData() {
		return CM_Mysql::select(TBL_CM_STREAM_SUBSCRIBE, '*', array('id' => $this->getId()))->fetchAssoc();
	}

	protected function _onDelete() {
		$this->getStreamChannel()->onUnsubscribe($this);
		CM_Mysql::delete(TBL_CM_STREAM_SUBSCRIBE, array('id' => $this->getId()));
	}

	/**
	 * @param string $key
	 */
	public static function findKey($key) {
		$id = CM_Mysql::select(TBL_CM_STREAM_SUBSCRIBE, 'id', array('key' => (string) $key))->fetchOne();
		if (!$id) {
			return null;
		}
		return new static($id);
	}

	/**
	 * @return CM_Model_User|null
	 */
	public function getUser() {
		if (is_null($this->_get('userId'))) {
			return null;
		}
		return CM_Model_User::factory($this->_get('userId'));
	}

	protected static function _create(array $data) {
		$userId = null;
		if (isset($data['user'])) {
			/** @var CM_Model_User $user */
			$user = $data['user'];
			$userId = $user->getId();
		}
		$start = (int) $data['start'];
		$allowedUntil = (int) $data['allowedUntil'];
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = $data['streamChannel'];
		$key = (string) $data['key'];
		$id = CM_Mysql::insert(TBL_CM_STREAM_SUBSCRIBE, array('userId' => $userId, 'start' => $start, 'allowedUntil' => $allowedUntil,
			'channelId' => $streamChannel->getId(), 'key' => $key));
		$streamSubscribe = new self($id);
		$streamChannel->onSubscribe($streamSubscribe);
		return $streamSubscribe;
	}

}
