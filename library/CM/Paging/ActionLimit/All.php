<?php

class CM_Paging_ActionLimit_All extends CM_Paging_ActionLimit_Abstract {

	/**
	 * @param int $type OPTIONAL
	 */
	public function __construct($type = null) {
		$where = null;
		if ($type) {
			$type = (int) $type;
			$where = '`type` = ' . $type;
		}
		$source = new CM_PagingSource_Sql('DISTINCT `actionType`, `actionVerb`, `type`', TBL_CM_ACTIONLIMIT,
			$where, '`type`, `actionType`, `actionVerb`');
		$source->enableCacheLocal();
		parent::__construct($source);
	}
}
