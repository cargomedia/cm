<?php

abstract class CM_Paging_Language_Abstract extends CM_Paging_Abstract {

	protected function _processItem($item) {
		return new CM_Model_Language($item);
	}

}