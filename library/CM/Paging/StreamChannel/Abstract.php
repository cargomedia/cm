<?php

class CM_Paging_StreamChannel_Abstract extends CM_Paging_Abstract {

	protected function _processItem($item) {
		return CM_Model_StreamChannel_Abstract::factory($item['id'], $item['type']);
	}

	/**
	 * @param CM_Model_StreamChannel_Abstract $streamChannel
	 * @return boolean
	 */
	public function contains(CM_Model_StreamChannel_Abstract $streamChannel) {
		return $this->_contains(array('id' => $streamChannel->getId(), 'type' => $streamChannel->getType()));
	}
}
