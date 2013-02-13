<?php

class CM_Model_StreamChannel_Video extends CM_Model_StreamChannel_Abstract {

	const TYPE = 19;

	public function onPublish(CM_Model_Stream_Publish $streamPublish) {
	}

	public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
	}

	public function onUnpublish(CM_Model_Stream_Publish $streamPublish) {
	}

	public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
	}

	/**
	 * @param int $thumbnailCount
	 */
	public function setThumbnailCount($thumbnailCount) {
		$thumbnailCount = (int) $thumbnailCount;
		CM_Mysql::update(TBL_CM_STREAMCHANNEL_VIDEO, array('thumbnailCount' => $thumbnailCount), array('id' => $this->getId()));
		$this->_change();
	}

	/**
	 * @return int
	 */
	public function getWidth() {
		return (int) $this->_get('width');
	}

	/**
	 * @return string
	 */
	public function getHash() {
		return md5($this->getStreamPublish()->getKey());
	}

	/**
	 * @return int
	 */
	public function getHeight() {
		return (int) $this->_get('height');
	}

	/**
	 * @return CM_Model_Stream_Publish
	 * @throws CM_Exception_Invalid
	 */
	public function getStreamPublish() {
		if (!$this->hasStreamPublish()) {
			throw new CM_Exception_Invalid('StreamChannel `' . $this->getId() . '` has no StreamPublish.');
		}
		return $this->getStreamPublishs()->getItem(0);
	}

	/**
	 * @return boolean
	 */
	public function hasStreamPublish() {
		return (boolean) $this->getStreamPublishs()->getCount();
	}

	/**
	 * @return string
	 */
	public function getPublicHost() {
		$serverId = (int) $this->_get('serverId');
		$serverArray = CM_Stream_Video::getInstance()->getServer($serverId);
		return (string) $serverArray['publicHost'];
	}

	/**
	 * @return string
	 */
	public function getPrivateHost() {
		$serverId = (int) $this->_get('serverId');
		$serverArray = CM_Stream_Video::getInstance()->getServer($serverId);
		return (string) $serverArray['privateIp'];
	}

	public function toArray() {
		$array = parent::toArray();
		if ($this->hasStreamPublish()) {
			$array['user'] = $this->getStreamPublish()->getUser();
		}
		return $array;
	}

	/**
	 * @return int
	 */
	public function getThumbnailCount() {
		return (int) $this->_get('thumbnailCount');
	}

	/**
	 * @return CM_Paging_FileUserContent_StreamChannelVideoThumbnails
	 */
	public function getThumbnails() {
		return new CM_Paging_FileUserContent_StreamChannelVideoThumbnails($this);
	}

	protected function _onBeforeDelete() {
		if ($this->hasStreamPublish()) {
			CM_Model_StreamChannelArchive_Video::create(array('streamChannel' => $this));
		}
	}

	protected function _onDelete() {
		CM_Mysql::delete(TBL_CM_STREAMCHANNEL_VIDEO, array('id' => $this->getId()));
		parent::_onDelete();
	}

	protected function _loadData() {
		return CM_Mysql::exec("SELECT * FROM TBL_CM_STREAMCHANNEL JOIN TBL_CM_STREAMCHANNEL_VIDEO USING (`id`) WHERE `id` = ?", $this->getId())->fetchAssoc();
	}

	protected static function _create(array $data) {
		$key = (string) $data['key'];
		$width = (int) $data['width'];
		$height = (int) $data['height'];
		$serverId = $data['serverId'];
		$thumbnailCount = (int) $data['thumbnailCount'];
		$adapterType = (int) $data['adapterType'];
		try {
		$id = CM_Mysql::insert(TBL_CM_STREAMCHANNEL, array('key' => $key, 'type' => static::TYPE, 'adapterType' => $adapterType));
			CM_Mysql::insert(TBL_CM_STREAMCHANNEL_VIDEO, array('id' => $id, 'width' => $width, 'height' => $height, 'serverId' => $serverId,
				'thumbnailCount' => $thumbnailCount));
		} catch (CM_Exception $ex) {
			CM_Mysql::delete(TBL_CM_STREAMCHANNEL, array('id' => $id));
			throw $ex;
		}
		return new static($id);
	}
}
