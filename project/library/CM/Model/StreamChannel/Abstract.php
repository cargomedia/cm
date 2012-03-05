<?php

class CM_Model_StreamChannel_Abstract extends CM_Model_Abstract {

	/**
	 * @var CM_Model_Stream_Publish
	 */
	private $_streamPublish;

	/**
	 * @return string
	 */
	public function getKey() {
		return (string) $this->_get('key');
	}

	/**
	 * @return CM_Paging_StreamPublish_StreamChannel
	 */
	public function getStreamPublishs() {
		return new CM_Paging_StreamPublish_StreamChannel($this);
	}

	/**
	 * @return CM_Model_Stream_Publish
	 */
	public function getStreamPublish() {
		return $this->_streamPublish;
	}

	/**
	 * @return CM_Paging_User_StreamChannel
	 */
	public function getVideoStreamSubscribers() {
		return new CM_Paging_User_StreamChannel($this);
	}

	/**
	 * @return CM_Paging_StreamSubscribe_StreamChannel
	 */
	public function getStreamSubscribes() {
		return new CM_Paging_StreamSubscribe_StreamChannel($this);
	}

	/**
	 * @param CM_Params|null $params
	 * @return boolean
	 */
	public function onPublish(CM_Params $params = null) {
		throw new CM_Exception_NotImplemented();
	}

	/**
	 * @param CM_Model_Stream_Subscribe $streamSubscribe
	 * @param CM_Params|null $params
	 * @return boolean
	 */
	public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe, CM_Params $params = null) {
		throw new CM_Exception_NotImplemented();
	}

	public function onUnpublish() {
		throw new CM_Exception_NotImplemented();
	}

	/**
	 * @param CM_Model_Stream_Subscribe $streamSubscribe
	 */
	public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
		throw new CM_Exception_NotImplemented();
	}

	protected function _loadData() {
		return CM_Mysql::select(TBL_CM_STREAMCHANNEL, 'key', array('id' => $this->getId()))->fetchAssoc();
	}

	protected function _onDelete() {
		/** @var CM_Model_Stream_Subscribe $videoStreamSubscribe */
		foreach ($this->getStreamSubscribes() as $videoStreamSubscribe) {
			$videoStreamSubscribe->delete();
		}
		/** @var CM_Model_Stream_Publish $streamPublish */
		foreach ($this->getStreamPublishs() as $streamPublish) {
			$streamPublish->delete();
		}
		CM_Mysql::delete(TBL_CM_STREAMCHANNEL, array('id' => $this->getId()));
	}

	/**
	 * @param int $id
	 * @param int|null $type
	 * @return CM_Model_StreamChannel_Video
	 * @throws CM_Exception_Invalid
	 */
	public static function factory($id, $type = null) {
		if (is_null($type)) {
			$type = CM_Mysql::select(TBL_CM_STREAMCHANNEL, 'type', array('id' => $id))->fetchOne();
		}
		switch ($type) {
			case CM_Model_StreamChannel_Video::TYPE:
				return new CM_Model_StreamChannel_Video($id);
			break;
			default:
				throw new CM_Exception_Invalid('Invalid StreamChannel type: `' . $type . '`');
		}
	}

	/**
	 * @param string $key
	 */
	public static function findKey($key) {
		$result = CM_Mysql::select(TBL_CM_STREAMCHANNEL, array('id', 'type'), array('key' => (string) $key))->fetchAssoc();
		if (!$result) {
			return null;
		}
		return self::factory($result['id'], $result['type']);
	}

	protected static function _create(array $data ) {
		$key = $data ['key'];
		$id = CM_Mysql::insert(TBL_CM_STREAMCHANNEL, array('key' => $key, 'type' => static::TYPE));
		return new static($id);
	}
}
