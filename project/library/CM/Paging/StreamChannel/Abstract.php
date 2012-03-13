<?php

class CM_Paging_StreamChannel_Abstract extends CM_Paging_Abstract {

	protected function _processItem($item) {
		return CM_Model_StreamChannel_Abstract::factory($item['id'], $item['type']);
	}
}
