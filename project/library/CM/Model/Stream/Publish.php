<?php

class CM_Model_Stream_Publish extends CM_Model_Stream_Abstract {

	const TYPE = 21;

	public function setAllowedUntil($timeStamp) {
		CM_Mysql::update(TBL_CM_STREAM_PUBLISH, array('allowedUntil' => $timeStamp), array('id' => $this->getId()));
		$this->_change();
	}

	protected function _getContainingCacheables() {
		$cacheables = parent::_getContainingCacheables();
		$cacheables[] = $this->getStreamChannel()->getStreamPublishs();
		$cacheables[] = $this->getStreamChannel()->getPublishers();
		return $cacheables;
	}

	protected function _loadData() {
		return CM_Mysql::select(TBL_CM_STREAM_PUBLISH, '*', array('id' => $this->getId()))->fetchAssoc();
	}

	protected function _onDelete() {
		$this->getStreamChannel()->onUnpublish($this);
		CM_Mysql::delete(TBL_CM_STREAM_PUBLISH, array('id' => $this->getId()));
	}

	/**
	 * @param string $key
	 */
	public static function findKey($key) {
		$id = CM_Mysql::select(TBL_CM_STREAM_PUBLISH, 'id', array('key' => (string) $key))->fetchOne();
		if (!$id) {
			return null;
		}
		return new static($id);
	}

	protected static function _create(array $data) {
		/** @var CM_Model_User $user */
		$user = $data['user'];
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = $data['streamChannel'];
		$start = (int) $data['start'];
		$allowedUntil = (int) $data['allowedUntil'];
		$key = (string) $data['key'];
		$id = CM_Mysql::insert(TBL_CM_STREAM_PUBLISH, array('userId' => $user->getId(), 'start' => $start, 'allowedUntil' => $allowedUntil,
			'key' => $key, 'channelId' => $streamChannel->getId()));
		$streamPublish = new self($id);
		$streamChannel->onPublish($streamPublish);
		return $streamPublish;
	}
}
