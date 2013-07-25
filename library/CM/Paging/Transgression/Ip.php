<?php

class CM_Paging_Transgression_Ip extends CM_Paging_Transgression_Abstract {

	private $_ip;

	/**
	 * @param int $ip
	 * @param int $actionType  OPTIONAL
	 * @param int $actionVerb OPTIONAL
	 * @param int $limitType  OPTIONAL
	 * @param int $period     OPTIONAL
	 */
	public function __construct($ip = null, $actionType = null, $actionVerb = null, $limitType = null, $period = null) {
		if ($ip) {
			$this->_ip = (int) $ip;
			$where = '`ip` = ' . $this->_ip;
		} else {
			$where = '`ip` IS NOT NULL';
		}
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
		CM_Db_Db::insertDelayed('cm_action', array('ip' => $this->_ip, 'verb' => $action->getVerb(), 'type' => $action->getType(),
				'actionLimitType' => $limitType, 'createStamp' => time()));
	}
}
