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
		$streamPublish->delete();
		$this->_change();
	}
}
