<?php

class CM_Paging_Location_Abstract extends CM_Paging_Abstract {

	protected function _processItem($item) {
		return new CM_Location($item['level'], $item['id']);
	}
}
