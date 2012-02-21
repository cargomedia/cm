<?php

class CM_VideoStream_Subscribe extends CM_VideoStream_Abstract {

	/**
	 * @return CM_VideoStream_Publish
	 */
	public function getVideoStreamPublish() {
		return new CM_VideoStream_Publish($this->_get('publishId'));
	}

	public function setAllowedUntil($timeStamp) {
		CM_Mysql::update(TBL_CM_VIDEOSTREAM_SUBSCRIBE, array('allowedUntil' => $timeStamp), array('id' => $this->getId()));
		$this->_change();
	}

	protected function _loadData() {
		return CM_Mysql::select(TBL_CM_VIDEOSTREAM_SUBSCRIBE, '*', array('id' => $this->getId()))->fetchAssoc();
	}

	protected function _onDelete() {
		CM_Mysql::delete(TBL_CM_VIDEOSTREAM_SUBSCRIBE, array('id' => $this->getId()));
	}

	/**
	 * @param string $key
	 */
	public static function findKey($key) {
		$id = CM_Mysql::select(TBL_CM_VIDEOSTREAM_SUBSCRIBE, 'id', array('key' => (string) $key))->fetchOne();
		if (!$id) {
			return null;
		}
		return new self($id);
	}

	protected static function _create(array $data) {
		/** @var CM_User $user */
		$user = $data['user'];
		$start = (int) $data['start'];
		$allowedUntil = (int) $data['allowedUntil'];
		/** @var CM_VideoStream_Publish $publish */
		$publish = $data['publish'];
		$key = (string) $data['key'];
		$id = CM_Mysql::insert(TBL_CM_VIDEOSTREAM_SUBSCRIBE, array('userId' => $user->getId(), 'start' => $start, 'allowedUntil' => $allowedUntil,
			'publishId' => $publish->getId(), 'key' => $key));
		return new self($id);
	}
}
