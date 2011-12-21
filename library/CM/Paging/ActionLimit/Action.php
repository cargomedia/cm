<?php

class CM_Paging_ActionLimit_Action extends CM_Paging_ActionLimit_Abstract {
	
	/**
	 * @param CM_Action_Abstract $action
	 */
	public function __construct(CM_Action_Abstract $action) {
		$source = new CM_PagingSource_Sql('DISTINCT `entityType`, `actionType`, `type`', TBL_ACTION_LIMIT, '`entityType` = ' . $action->getEntityType() . ' AND `actionType` = ' . $action->getType(), '`type`');
		$source->enableCacheLocal();
		parent::__construct($source);
	}
	
}
