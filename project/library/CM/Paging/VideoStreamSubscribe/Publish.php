<?php

class CM_Paging_VideoStreamSubscribe_Publish extends CM_Paging_VideoStreamSubscribe_Abstract {

	private $_videoStreamPublish;

	/**
	 * @param CM_VideoStream_Publish $videoStreamPublish
	 */
	public function __construct(CM_VideoStream_Publish $videoStreamPublish) {
		$this->_videoStreamPublish = $videoStreamPublish;
		$source = new CM_PagingSource_Sql('`id`', TBL_CM_VIDEOSTREAM_SUBSCRIBE, '`publishId` = ' . $videoStreamPublish->getId());
		$source->enableCache();
		return parent::__construct($source);
	}

	/**
	 * @param array $data
	 */
	public function add(array $data) {
		$data['publish'] = $this->_videoStreamPublish;
		CM_VideoStream_Subscribe::create($data);
		$this->_change();
	}

	/**
	 * @param CM_VideoStream_Subscribe $videoStreamSubscribe
	 */
	public function delete(CM_VideoStream_Subscribe $videoStreamSubscribe) {
		if (!$this->contains($videoStreamSubscribe)) {
			throw new CM_Exception_Invalid("Cannot remove a videoStream from a videoStream it doesn' subscribe to.");
		}
		$videoStreamSubscribe->delete();
		$this->_change();
	}
}
