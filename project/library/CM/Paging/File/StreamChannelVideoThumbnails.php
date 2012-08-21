<?php

class CM_Paging_File_StreamChannelVideoThumbnails extends CM_Paging_Abstract {

	/**
	 * @var CM_Model_StreamChannel_Video
	 */
	private $_streamChannel;

	/**
	 * @param CM_Model_StreamChannel_Video $streamChannel
	 */
	public function __construct(CM_Model_StreamChannel_Video $streamChannel) {
		$this->_streamChannel = $streamChannel;
		if ($this->_streamChannel->getThumbnailCount()) {
			$input = range(1, $this->_streamChannel->getThumbnailCount());
		} else {
			$input = array();
		}
		$source = new CM_PagingSource_Array($input);
		parent::__construct($source);
	}

	protected function _processItem($item) {
		return $this->_streamChannel->getThumbnail($item);
	}
}