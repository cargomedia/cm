<?php

class CM_Paging_StreamPublish_StreamChannel extends CM_Paging_StreamPublish_Abstract {

	private $_streamChannel;

	public function __construct(CM_Model_StreamChannel_Abstract $streamChannel) {
		$this->_streamChannel = $streamChannel;
		$source = new CM_PagingSource_Sql('id', TBL_CM_STREAM_PUBLISH, '`channelId` = ' . $this->_streamChannel->getId());
		$source->enableCache();
		parent::__construct($source);
	}

	/**
	 * @param array $data
	 * @return CM_Model_Stream_Publish
	 */
	public function add(array $data) {
		$data['streamChannel'] = $this->_streamChannel;
		$streamPublish = CM_Model_Stream_Publish::create($data);
		$this->_change();
		$this->_streamChannel->onPublish($streamPublish);
		return $streamPublish;
	}

	/**
	 * @param CM_Model_Stream_Publish $streamPublish
	 * @throws CM_Exception_Invalid
	 */
	public function delete(CM_Model_Stream_Publish $streamPublish) {
		if (!$this->contains($streamPublish)) {
			throw new CM_Exception_Invalid("Cannot remove a stream from a channel it doesn't publish to.");
		}
		$this->_streamChannel->onUnpublish($streamPublish);
		$streamPublish->delete();
		$this->_change();
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
