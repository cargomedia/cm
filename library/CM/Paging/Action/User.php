<?php

class CM_Paging_Action_User extends CM_Paging_Action_Abstract {

	/** @var CM_Model_User */
	private $_user;

	/**
	 * @param CM_Model_User $user
	 * @param int|null      $actionType
	 * @param int|null      $actionVerb
	 * @param int|null      $period
	 * @param int|null      $upperBound
	 */
	public function __construct(CM_Model_User $user, $actionType = null, $actionVerb = null, $period = null, $upperBound = null) {
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
			$upperBound = (null !== $upperBound) ? (int) $upperBound : time();
			$lowerBound = $upperBound - $period;
			$where .= ' AND `createStamp` > ' . $lowerBound . ' AND `createStamp` <= ' . $upperBound;
		}
		$source = new CM_PagingSource_Sql_Deferred('type, verb, createStamp', 'cm_action', $where, '`createStamp` DESC');
		parent::__construct($source);
	}

	public function add(CM_Action_Abstract $action) {
		CM_Db_Db::insertDelayed('cm_action',
			array('actorId' => $this->_user->getId(), 'verb' => $action->getVerb(), 'type' => $action->getType(), 'createStamp' => time()));
	}
}
