<?php

abstract class CM_Paging_StreamSubscribe_Abstract extends CM_Paging_Abstract {

	protected function _processItem($item) {
		return new CM_Model_Stream_Subscribe($item['id']);
	}

	/**
	 * @param CM_Model_Stream_Subscribe $videoStreamSubscribe
	 * @return bool
	 */
	public function contains(CM_Model_Stream_Subscribe $videoStreamSubscribe) {
		return in_array($videoStreamSubscribe->getId(), $this->getItemsRaw());
	}

}
