<?php

class CM_Model_Stream_Publish extends CM_Model_Stream_Abstract {

	const TYPE = 21;

	public function setAllowedUntil($timeStamp) {
		CM_Db_Db::update('cm_stream_publish', array('allowedUntil' => $timeStamp), array('id' => $this->getId()));
		$this->_change();
	}

	public function unsetUser() {
		CM_Db_Db::update('cm_stream_publish', array('userId' => null), array('id' => $this->getId()));
		$this->_change();
	}

	protected function _getContainingCacheables() {
		$cacheables = parent::_getContainingCacheables();
		$cacheables[] = $this->getStreamChannel()->getStreamPublishs();
		$cacheables[] = $this->getStreamChannel()->getPublishers();
		return $cacheables;
	}

	protected function _loadData() {
		return CM_Db_Db::select('cm_stream_publish', '*', array('id' => $this->getId()))->fetch();
	}

	protected function _onDeleteBefore() {
		$streamChannel = $this->getStreamChannel();
		if ($streamChannel->isValid()) {
			$streamChannel->onUnpublish($this);
		}
	}

	protected function _onDelete() {
		CM_Db_Db::delete('cm_stream_publish', array('id' => $this->getId()));
	}

	/**
	 * @param string                          $key
	 * @param CM_Model_StreamChannel_Abstract $channel
	 * @return CM_Model_Stream_Publish|null
	 */
	public static function findByKeyAndChannel($key, CM_Model_StreamChannel_Abstract $channel) {
		$id = CM_Db_Db::select('cm_stream_publish', 'id', array('key' => (string) $key, 'channelId' => $channel->getId()))->fetchColumn();
		if (!$id) {
			return null;
		}
		return new static($id);
	}

	protected static function _createStatic(array $data) {
		/** @var CM_Model_User $user */
		$user = $data['user'];
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = $data['streamChannel'];
		$start = (int) $data['start'];

		if (!$streamChannel->isValid()) {
			throw new CM_Exception_Invalid('Stream channel not valid', null, null, CM_Exception::WARN);
		}

		$allowedUntil = $streamChannel->canPublish($user, time());
		if ($allowedUntil <= time()) {
			throw new CM_Exception_NotAllowed('Not allowed to publish');
		}

		$key = (string) $data['key'];
		$id = CM_Db_Db::insert('cm_stream_publish', array(
			'userId'       => $user->getId(),
			'start'        => $start,
			'allowedUntil' => $allowedUntil,
			'key'          => $key,
			'channelId'    => $streamChannel->getId(),
		));
		$streamPublish = new self($id);
		$streamChannel->onPublish($streamPublish);
		return $streamPublish;
	}
}
