<?php

class CM_Paging_ActionLimit_All extends CM_Paging_ActionLimit_Abstract {
	
	/**
	 * @param int $type OPTIONAL
	 */
	public function __construct($type = null) {
		$where = null;
		if ($type) {
			$where = '`type` = ' . $type;
		}
		$source = new CM_PagingSource_Sql('DISTINCT `entityType`, `actionType`, `type`', TBL_CM_ACTIONLIMIT, $where, '`type`, `entityType`, `actionType`');
		$source->enableCacheLocal();
		parent::__construct($source);
	}
}
