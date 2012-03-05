<?php

class CM_Model_Stream_Subscribe extends CM_Model_Stream_Abstract {

	/**
	 * @return CM_Model_StreamChannel_Abstract
	 */
	public function getStreamChannel() {
		return CM_Model_StreamChannel_Abstract::factory($this->_get('channelId'));
	}

	public function setAllowedUntil($timeStamp) {
		CM_Mysql::update(TBL_CM_STREAM_SUBSCRIBE, array('allowedUntil' => $timeStamp), array('id' => $this->getId()));
		$this->_change();
	}

	protected function _loadData() {
		return CM_Mysql::select(TBL_CM_STREAM_SUBSCRIBE, '*', array('id' => $this->getId()))->fetchAssoc();
	}

	protected function _onDelete() {
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
		return new self($id);
	}

	protected static function _create(array $data) {
		/** @var CM_User $user */
		$user = $data['user'];
		$start = (int) $data['start'];
		$allowedUntil = (int) $data['allowedUntil'];
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = $data['streamChannel'];
		$key = (string) $data['key'];
		$id = CM_Mysql::insert(TBL_CM_STREAM_SUBSCRIBE, array('userId' => $user->getId(), 'start' => $start, 'allowedUntil' => $allowedUntil,
			'channelId' => $streamChannel->getId(), 'key' => $key));
		return new self($id);
	}
}
