<?php

class CM_Paging_Action_User extends CM_Paging_Action_Abstract {
	
	private $_user;
	
	/**
	 * @param CM_Model_User $user
	 * @param int $entityType OPTIONAL
	 * @param int $actionType OPTIONAL
	 * @param int $period OPTIONAL
	 */
	public function __construct(CM_Model_User $user, $entityType = null, $actionType = null, $period = null) {
		$this->_user = $user;
		$period = (int) $period;
		$where = 'actorId=' . $user->getId() . ' AND `actionLimitType` IS NULL';
		if ($entityType) {
			$entityType = (int) $entityType;
			$where .= ' AND `entityType` = ' . $entityType;
		}
		if ($actionType) {
			$actionType = (int) $actionType;
			$where .= ' AND `actionType` = ' . $actionType;
		}
		if ($period) {
			$time = time() - $period;
			$where .= ' AND `createStamp` > ' . $time;
		}
		$source = new CM_PagingSource_Sql_Deferred('entityType, actionType, createStamp', TBL_CM_ACTION, $where, '`createStamp` DESC');
		parent::__construct($source);
	}
	
	public function add(CM_Action_Abstract $action) {
		CM_Mysql::insertDelayed(TBL_CM_ACTION,
				array('actorId' => $this->_user->getId(), 'actionType' => $action->getType(), 'entityType' => $action->getEntityType(), 'createStamp' => time()));
	}
}
