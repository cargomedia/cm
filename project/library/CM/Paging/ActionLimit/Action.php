<?php

class CM_Paging_ActionLimit_Action extends CM_Paging_ActionLimit_Abstract {
	
	/**
	 * @param CM_Action_Abstract $action
	 */
	public function __construct(CM_Action_Abstract $action) {
		$source = new CM_PagingSource_Sql('DISTINCT `modelType`, `actionType`, `type`', TBL_CM_ACTIONLIMIT, '`modelType` = ' . $action->getModelType() . ' AND `actionType` = ' . $action->getType(), '`type`');
		$source->enableCacheLocal();
		parent::__construct($source);
	}
	
}
