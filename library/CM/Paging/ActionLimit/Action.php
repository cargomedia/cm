<?php

class CM_Paging_ActionLimit_Action extends CM_Paging_ActionLimit_Abstract {

	/**
	 * @param CM_Action_Abstract $action
	 */
	public function __construct(CM_Action_Abstract $action) {
		$source = new CM_PagingSource_Sql('DISTINCT `actionType`, `actionVerb`, `type`', 'cm_actionLimit',
				'`actionType` = ' . $action->getType() . ' AND `actionVerb` = ' . $action->getVerb(), '`type`');
		$source->enableCacheLocal();
		parent::__construct($source);
	}

}
