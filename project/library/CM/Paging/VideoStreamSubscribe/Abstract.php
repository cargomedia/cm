<?php

abstract class CM_Paging_VideoStreamSubscribe_Abstract extends CM_Paging_Abstract {

	protected function _processItem($item) {
		return new CM_VideoStream_Subscribe($item['id']);
	}

	/**
	 * @param CM_VideoStream_Subscribe $videoStreamSubscribe
	 * @return bool
	 */
	public function contains(CM_VideoStream_Subscribe $videoStreamSubscribe) {
		return in_array($videoStreamSubscribe->getId(), $this->getItemsRaw());
	}

}
