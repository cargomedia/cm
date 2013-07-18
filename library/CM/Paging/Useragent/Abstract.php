<?php

abstract class CM_Paging_Useragent_Abstract extends CM_Paging_Abstract {

	protected function _processItem($itemRaw) {
		$item = array();
		$item['useragent'] = (string) $itemRaw['useragent'];
		$item['createStamp'] = (int) $itemRaw['createStamp'];
		return $item;
	}

	/**
	 * @param int $age
	 */
	public static function deleteOlder($age) {
		$age = (int) $age;
		CM_Db_Db::delete('cm_useragent', '`createStamp` < ' . (time() - $age));
	}
}
