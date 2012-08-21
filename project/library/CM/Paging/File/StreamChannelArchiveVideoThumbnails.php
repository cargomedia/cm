<?php

class CM_Paging_File_StreamChannelArchiveVideoThumbnails extends CM_Paging_Abstract {

	/**
	 * @var CM_Model_StreamChannelArchive_Video
	 */
	private $_streamChannelArchive;

	/**
	 * @param CM_Model_StreamChannelArchive_Video $streamChannelArchive
	 */
	public function __construct(CM_Model_StreamChannelArchive_Video $streamChannelArchive) {
		$this->_streamChannelArchive = $streamChannelArchive;
		if ($this->_streamChannelArchive->getThumbnailCount()) {
			$input = range(1, $this->_streamChannelArchive->getThumbnailCount());
		} else {
			$input = array();
		}
		$source = new CM_PagingSource_Array($input);
		parent::__construct($source);
	}

	protected function _processItem($item) {
		return $this->_streamChannelArchive->getThumbnail($item);
	}
}