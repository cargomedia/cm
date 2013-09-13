<?php

class CM_Paging_StreamPublish_StreamChannel extends CM_Paging_StreamPublish_Abstract {

	/**
	 * @param CM_Model_StreamChannel_Abstract $streamChannel
	 */
	public function __construct(CM_Model_StreamChannel_Abstract $streamChannel) {
		$source = new CM_PagingSource_Sql('id', 'cm_stream_publish', '`channelId` = ' . $streamChannel->getId());
		$source->enableCache();
		parent::__construct($source);
	}

	/**
	 * @param string $key
	 * @return CM_Model_Stream_Publish|null
	 */
	public function findKey($key) {
		$key = (string) $key;
		/** @var CM_Model_Stream_Publish $streamPublish */
		foreach($this as $streamPublish) {
			if ($streamPublish->getKey() == $key) {
				return $streamPublish;
			}
		}
		return null;
	}
}
