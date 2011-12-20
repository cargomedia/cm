<?php

abstract class CM_Paging_Smiley_Abstract extends CM_Paging_Abstract {

	protected function _processItem($itemRaw) {
		$item = array();
		$item['id'] =  (int) $itemRaw['id'];
		$item['codes'] = explode(',', $itemRaw['code']);
		$item['section'] = (int) $itemRaw['section'];
		$item['path'] = $itemRaw['section'] . '/' . $itemRaw['file'];
		return $item;
	}
}
