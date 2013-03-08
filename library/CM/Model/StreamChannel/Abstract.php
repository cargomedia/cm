<?php

abstract class CM_Model_StreamChannel_Abstract extends CM_Model_Abstract {

	/**
	 * @param CM_Model_User $user
	 * @param int           $allowedUntil
	 * @return int
	 */
	public function canPublish(CM_Model_User $user, $allowedUntil) {
		return $allowedUntil + 1000;
	}

	/**
	 * @param CM_Model_User|null $user
	 * @param int                $allowedUntil
	 * @return int
	 */
	public function canSubscribe(CM_Model_User $user = null, $allowedUntil) {
		return $allowedUntil + 1000;
	}

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
	 * @return int
	 */
	public function getAdapterType() {
		return (int) $this->_get('adapterType');
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
		/** @var CM_Model_Stream_Subscribe $streamSubscribe */
		foreach ($this->getStreamSubscribes() as $streamSubscribe) {
			$streamSubscribe->delete();
		}
		/** @var CM_Model_Stream_Publish $streamPublish */
		foreach ($this->getStreamPublishs() as $streamPublish) {
			$streamPublish->delete();
		}
		CM_Db_Db::delete(TBL_CM_STREAMCHANNEL, array('id' => $this->getId()));
	}

	/**
	 * @param int      $id
	 * @param int|null $type
	 * @return CM_Model_StreamChannel_Abstract
	 * @throws CM_Exception_Invalid
	 */
	public static function factory($id, $type = null) {
		if (null === $type) {
			$cacheKey = CM_CacheConst::StreamChannel_Type . '_id:' . $id;
			if (false === ($type = CM_Cache::get($cacheKey))) {
				$type = CM_Mysql::select(TBL_CM_STREAMCHANNEL, 'type', array('id' => $id))->fetchOne();
				CM_Cache::set($cacheKey, $type);
			}
		}
		$class = self::_getClassName($type);
		return new $class($id);
	}

	/**
	 * @param string $key
	 * @param int    $adapterType
	 * @return CM_Model_StreamChannel_Abstract|null
	 */
	public static function findByKey($key, $adapterType) {
		$key = (string) $key;
		$adapterType = (int) $adapterType;
		$result = CM_Mysql::select(TBL_CM_STREAMCHANNEL, array('id', 'type'), array('key' => $key, 'adapterType' => $adapterType))->fetchAssoc();
		if (!$result) {
			return null;
		}
		return self::factory($result['id'], $result['type']);
	}

	/**
	 * @param string $key
	 * @param int    $adapterType
	 * @return CM_Model_StreamChannel_Abstract
	 */
	public static function getByKey($key, $adapterType) {
		$streamChannel = static::findByKey($key, $adapterType);
		if (!$streamChannel) {
			$streamChannel = static::create(array('key' => $key, 'adapterType' => $adapterType));
		}
		return $streamChannel;
	}

	/**
	 * @param int $type
	 * @return CM_Paging_StreamChannel_Type
	 */
	public static function getAllByType($type) {
		return new CM_Paging_StreamChannel_Type(array($type));
	}

	protected static function _create(array $data) {
		$key = (string) $data ['key'];
		$adapterType = (int) $data['adapterType'];
		$id = CM_Mysql::insert(TBL_CM_STREAMCHANNEL, array('key' => $key, 'type' => static::TYPE, 'adapterType' => $adapterType));
		return new static($id);
	}
}
