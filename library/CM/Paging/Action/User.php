<?php

class CM_Paging_Action_User extends CM_Paging_Action_Abstract {

	private $_user;

	/**
	 * @param CM_Model_User $user
	 * @param int $actionType OPTIONAL
	 * @param int $actionVerb OPTIONAL
	 * @param int $period OPTIONAL
	 */
	public function __construct(CM_Model_User $user, $actionType = null, $actionVerb = null, $period = null) {
		$this->_user = $user;
		$period = (int) $period;
		$where = 'actorId=' . $user->getId() . ' AND `actionLimitType` IS NULL';
		if ($actionType) {
			$actionType = (int) $actionType;
			$where .= ' AND `type` = ' . $actionType;
		}
		if ($actionVerb) {
			$actionVerb = (int) $actionVerb;
			$where .= ' AND `verb` = ' . $actionVerb;
		}
		if ($period) {
			$time = time() - $period;
			$where .= ' AND `createStamp` > ' . $time;
		}
		$source = new CM_PagingSource_Sql_Deferred('type, verb, createStamp', 'cm_action', $where, '`createStamp` DESC');
		parent::__construct($source);
	}

	public function add(CM_Action_Abstract $action) {
		CM_Db_Db::insertDelayed('cm_action',
				array('actorId' => $this->_user->getId(), 'verb' => $action->getVerb(), 'type' => $action->getType(), 'createStamp' => time()));
	}
}
