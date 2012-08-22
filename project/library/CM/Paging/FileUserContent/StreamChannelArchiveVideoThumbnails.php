<?php

class CM_Paging_FileUserContent_StreamChannelArchiveVideoThumbnails extends CM_Paging_FileUserContent_Abstract {

	/**
	 * @var CM_Model_StreamChannelArchive_Video
	 */
	private $_streamChannelArchive;

	/**
	 * @param CM_Model_StreamChannelArchive_Video $streamChannelArchive
	 */
	public function __construct(CM_Model_StreamChannelArchive_Video $streamChannelArchive) {
		$this->_streamChannelArchive = $streamChannelArchive;
		$input = $this->_streamChannelArchive->getThumbnailCount() ? range(1, $this->_streamChannelArchive->getThumbnailCount()) : array();
		$source = new CM_PagingSource_Array($input);
		parent::__construct($source);
	}

	protected function _getFilename($item) {
		return $this->_streamChannelArchive->getId() . '-' . $this->_streamChannelArchive->getHash() . '-thumbs' . DIRECTORY_SEPARATOR . $item . '.jpg';
	}

	protected function _getNamespace($item) {
		return 'streamChannels';
	}

	protected function _getSequence($item) {
		return $this->_streamChannelArchive->getId();
	}

}