<?php

abstract class CM_Paging_User_Abstract extends CM_Paging_Abstract {

	protected function _processItem($itemRaw) {
		return CM_Model_User::factory($itemRaw);
	}
}
