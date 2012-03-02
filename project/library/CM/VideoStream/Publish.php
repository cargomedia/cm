<?php

class CM_VideoStream_Publish extends CM_VideoStream_Abstract {

    /**
     * @return int
     */
    public function getDelegateType() {
        return (int) $this->_get('delegateType');
    }

	/**
	 * @return string
	 */
	public function getName() {
		return (string) $this->_get('name');
	}

	/**
	 * @return CM_StreamChannel
	 */
	public function getStreamChannel() {
		return new CM_StreamChannel($this);
	}

	public function setAllowedUntil($timeStamp) {
		CM_Mysql::update(TBL_CM_VIDEOSTREAM_PUBLISH, array('allowedUntil' => $timeStamp), array('id' => $this->getId()));
		$this->_change();
	}

	protected function _loadData() {
		return CM_Mysql::exec("SELECT * FROM TBL_CM_VIDEOSTREAM_PUBLISH WHERE `id` = " . $this->getId())->fetchAssoc();
	}

	protected function _onDelete() {
		/** @var CM_VideoStream_Subscribe $videoStreamSubscribe */
		foreach ($this->getStreamChannel()->getVideoStreamSubscribes() as $videoStreamSubscribe) {
			$videoStreamSubscribe->delete();
		}
		CM_Mysql::delete(TBL_CM_VIDEOSTREAM_PUBLISH, array('id' => $this->getId()));
	}

	/**
	 * @param string $key
	 */
	public static function findKey($key) {
		$id = CM_Mysql::select(TBL_CM_VIDEOSTREAM_PUBLISH, 'id', array('key' => (string) $key))->fetchOne();
		if (!$id) {
			return null;
		}
		return new static($id);
	}

	/**
	 * @param string $name
	 * @return CM_VideoStream_Publish|null
	 */
	public static function findStreamName($name) {
		$id = CM_Mysql::select(TBL_CM_VIDEOSTREAM_PUBLISH, 'id', array('name' => (string) $name))->fetchOne();
		if (!$id) {
			return null;
		}
		return new static($id);
	}

	protected static function _create(array $data) {
		/** @var CM_User $user */
		$user = $data['user'];
		$start = (int) $data['start'];
		$allowedUntil = (int) $data['allowedUntil'];
		$key = (string) $data['key'];
		$name = (string) $data['name'];
        $delegateType = (int) $data['delegateType'];
		$id = CM_Mysql::insert(TBL_CM_VIDEOSTREAM_PUBLISH, array('userId' => $user->getId(), 'start' => $start, 'allowedUntil' => $allowedUntil,
			'key' => $key, 'name' => $name, 'delegateType' => $delegateType));
		return new self($id);
	}
}
