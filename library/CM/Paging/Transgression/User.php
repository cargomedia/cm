<?php

class CM_Paging_Transgression_User extends CM_Paging_Transgression_Abstract {

	private $_user;

	/**
	 * @param CM_Model_User $user
	 * @param int $actionType OPTIONAL
	 * @param int $actionVerb OPTIONAL
	 * @param int $limitType OPTIONAL
	 * @param int $period OPTIONAL
	 */
	public function __construct(CM_Model_User $user, $actionType = null, $actionVerb = null, $limitType = null, $period = null) {
		$this->_user = $user;
		$where = '`actorId` = ' . $user->getId();
		if ($actionType) {
			$actionType = (int) $actionType;
			$where .= ' AND `type` = ' . $actionType;
		}
		if ($actionVerb) {
			$actionVerb = (int) $actionVerb;
			$where .= ' AND `verb` = ' . $actionVerb;
		}
		if ($limitType) {
			$limitType = (int) $limitType;
			$where .= ' AND `actionLimitType` = ' . $limitType;
		} else {
			$where .= ' AND `actionLimitType` IS NOT NULL';
		}
		if ($period) {
			$period = (int) $period;
			$time = time() - $period;
			$where .= ' AND `createStamp` > ' . $time;
		}
		$source = new CM_PagingSource_Sql_Deferred('type, verb, createStamp', 'cm_action', $where, '`createStamp` DESC');
		parent::__construct($source);
	}

	public function add(CM_Action_Abstract $action, $limitType) {
		$limitType = (int) $limitType;
		CM_Db_Db::insertDelayed('cm_action',
				array('actorId' => $this->_user->getId(), 'verb' => $action->getVerb(), 'type' => $action->getType(), 'actionLimitType' => $limitType, 'createStamp' => time()));
	}

	public function deleteAll() {
		CM_Db_Db::delete('cm_action', '`actorId` = ' . $this->_user->getId() . ' AND `actionLimitType` IS NOT NULL');
	}
}
