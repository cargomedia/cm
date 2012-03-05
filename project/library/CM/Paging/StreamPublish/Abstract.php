<?php

class CM_Paging_StreamPublish_Abstract extends CM_Paging_Abstract {

	/**
	 * @param CM_Model_Stream_Publish $streamPublish
	 * @return boolean
	 */
	public function contains(CM_Model_Stream_Publish $streamPublish) {
		return in_array($streamPublish->getId(), $this->getItemsRaw());
	}

	protected function _processItem($item) {
		return new CM_Model_Stream_Publish($item);
	}
}
