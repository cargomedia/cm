<?php

class CM_Paging_FileUserContent_StreamChannelVideoThumbnails extends CM_Paging_FileUserContent_Abstract {

	/**
	 * @var CM_Model_StreamChannel_Video
	 */
	private $_streamChannel;

	/**
	 * @param CM_Model_StreamChannel_Video $streamChannel
	 */
	public function __construct(CM_Model_StreamChannel_Video $streamChannel) {
		$this->_streamChannel = $streamChannel;
		$input = $this->_streamChannel->getThumbnailCount() ? range(1, $this->_streamChannel->getThumbnailCount()) : array();
		$source = new CM_PagingSource_Array($input);
		parent::__construct($source);
	}

	protected function _getFilename($item) {
		return $this->_streamChannel->getId() . '-' . $this->_streamChannel->getHash() . '-thumbs' . DIRECTORY_SEPARATOR . $item . '.jpg';
	}

	protected function _getFileNamespace($item) {
		return 'streamChannels';
	}

	protected function _getSequence($item) {
		return (int) $this->_streamChannel->getId();
	}

}