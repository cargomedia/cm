<?php

class CM_Paging_VideoStreamSubscribe_StreamChannel extends CM_Paging_VideoStreamSubscribe_Abstract {

	private $_streamChannel;

	/**
	 * @param CM_VideoStream_Publish $streamChannel
	 */
	public function __construct(CM_StreamChannel $streamChannel) {
		$this->_streamChannel = $streamChannel;
		$source = new CM_PagingSource_Sql('`id`', TBL_CM_VIDEOSTREAM_SUBSCRIBE, '`publishId` = ' . $streamChannel->getStreamPublish()->getId());
		$source->enableCache();
		return parent::__construct($source);
	}

	/**
	 * @param array $data
	 * @return CM_VideoStream_Subscribe
	 */
	public function add(array $data) {
		$data['publish'] = $this->_streamChannel->getStreamPublish();
		$videoStreamSubscribe = CM_VideoStream_Subscribe::create($data);
		$this->_change();
		return $videoStreamSubscribe;
	}

	/**
	 * @param CM_VideoStream_Subscribe $videoStreamSubscribe
	 */
	public function delete(CM_VideoStream_Subscribe $videoStreamSubscribe) {
		if (!$this->contains($videoStreamSubscribe)) {
			throw new CM_Exception_Invalid("Cannot remove a videoStream from a videoStream it doesn't subscribe to.");
		}
		$videoStreamSubscribe->delete();
		$this->_change();
	}
}
