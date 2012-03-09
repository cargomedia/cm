<?php

abstract class CM_Model_StreamChannel_Abstract extends CM_Model_Abstract {

	/**
	 * @param CM_Model_User  $user
	 * @param CM_Params|null $params
	 * @return boolean
	 */
	abstract public function canPublish(CM_Model_User $user, CM_Params $params = null);

	/**
	 * @param CM_Model_User  $user
	 * @param CM_Params|null $params
	 * @return boolean
	 */
	abstract public function canSubscribe(CM_Model_User $user, CM_Params $params = null);

	/**
	 * @param CM_Model_Stream_Publish $streamPublish
	 */
	abstract public function onPublish(CM_Model_Stream_Publish $streamPublish);

	/**
	 * @param CM_Model_Stream_Subscribe $streamSubscribe
	 */
	abstract public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe);

	/**
	 * @param CM_Model_Stream_Publish $streamPublish
	 */
	abstract public function onUnpublish(CM_Model_Stream_Publish $streamPublish);

	/**
	 * @param CM_Model_Stream_Subscribe $streamSubscribe
	 */
	abstract public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe);

	/**
	 * @return string
	 */
	public function getKey() {
		return (string) $this->_get('key');
	}

	/**
	 * @return CM_Paging_User_StreamChannelPublisher
	 */
	public function getPublishers() {
		return new CM_Paging_User_StreamChannelPublisher($this);
	}

	/**
	 * @return CM_Paging_StreamPublish_StreamChannel
	 */
	public function getStreamPublishs() {
		return new CM_Paging_StreamPublish_StreamChannel($this);
	}

	/**
	 * @return CM_Paging_StreamSubscribe_StreamChannel
	 */
	public function getStreamSubscribes() {
		return new CM_Paging_StreamSubscribe_StreamChannel($this);
	}

	/**
	 * @return CM_Paging_User_StreamChannelSubscriber
	 */
	public function getSubscribers() {
		return new CM_Paging_User_StreamChannelSubscriber($this);
	}

	/**
	 * @return CM_Paging_User_StreamChannel
	 */
	public function getUsers() {
		return new CM_Paging_User_StreamChannel($this);
	}

	protected function _loadData() {
		return CM_Mysql::select(TBL_CM_STREAMCHANNEL, 'key', array('id' => $this->getId()))->fetchAssoc();
	}

	protected function _onDelete() {
		$streamSubscribes = $this->getStreamSubscribes();
		/** @var CM_Model_Stream_Subscribe $streamSubscribe */
		foreach ($streamSubscribes as $streamSubscribe) {
			$streamSubscribes->delete($streamSubscribe);
		}
		$streamPublishs = $this->getStreamPublishs();
		/** @var CM_Model_Stream_Publish $streamPublish */
		foreach ($streamPublishs as $streamPublish) {
			$streamPublishs->delete($streamPublish);
		}
		CM_Mysql::delete(TBL_CM_STREAMCHANNEL, array('id' => $this->getId()));
	}

	/**
	 * @param int      $id
	 * @param int|null $type
	 * @return CM_Model_StreamChannel_Abstract
	 * @throws CM_Exception_Invalid
	 */
	public static function factory($id, $type = null) {
		if (is_null($type)) {
			$type = CM_Mysql::select(TBL_CM_STREAMCHANNEL, 'type', array('id' => $id))->fetchOne();
		}
		$class = self::_getClassName($type);
		return new $class($id);
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

	protected static function _create(array $data) {
		$key = (string) $data ['key'];
		$id = CM_Mysql::insert(TBL_CM_STREAMCHANNEL, array('key' => $key, 'type' => static::TYPE));
		return new static($id);
	}
}
