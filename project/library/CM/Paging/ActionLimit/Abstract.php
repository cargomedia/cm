<?php

abstract class CM_Paging_ActionLimit_Abstract extends CM_Paging_Abstract {
	
	protected function _processItem($item) {
		return CM_Model_ActionLimit_Abstract::factory($item['type'], $item['entityType'], $item['actionType']);
	}
}
