<?php

abstract class CM_Paging_SplittestVariation_Abstract extends CM_Paging_Abstract {

	protected function _processItem($itemRaw) {
		return new CM_Model_SplittestVariation($itemRaw);
	}
}
