<?php

class CM_Paging_StreamSubscribe_StreamChannel extends CM_Paging_StreamSubscribe_Abstract {

	private $_streamChannel;

	/**
	 * @param CM_Model_StreamChannel_Abstract $streamChannel
	 */
	public function __construct(CM_Model_StreamChannel_Abstract $streamChannel) {
		$this->_streamChannel = $streamChannel;
		$source = new CM_PagingSource_Sql('`id`', TBL_CM_STREAM_SUBSCRIBE, '`channelId` = ' . $streamChannel->getId());
		$source->enableCache();
		return parent::__construct($source);
	}

	/**
	 * @param string $key
	 * @return CM_Model_Stream_Subscribe|null
	 */
	public function findKey($key) {
		$key = (string) $key;
		/** @var CM_Model_Stream_Subscribe $streamSubscribe */
		foreach($this as $streamSubscribe) {
			if ($streamSubscribe->getKey() == $key) {
				return $streamSubscribe;
			}
		}
		return null;
	}
}
