<?php

class CM_Paging_Action_Ip extends CM_Paging_Action_Abstract {
	
	private $_ip;
	
	/**
	 * @param int $ip
	 * @param int $actionType OPTIONAL
	 * @param int $actionVerb OPTIONAL
	 * @param int $period OPTIONAL
	 */
	public function __construct($ip, $actionType = null, $actionVerb = null, $period = null) {
		$this->_ip = (int) $ip;
		$period = (int) $period;
		$where = 'ip=' . $this->_ip . ' AND `actionLimitType` IS NULL';
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
		$source = new CM_PagingSource_Sql_Deferred('type, verb, createStamp', TBL_CM_ACTION, $where, '`createStamp` DESC');
		parent::__construct($source);
	}
	
	public function add(CM_Action_Abstract $action) {
		CM_Mysql::insertDelayed(TBL_CM_ACTION,
				array('ip' => $this->_ip, 'verb' => $action->getVerb(), 'type' => $action->getType(), 'createStamp' => time()));
	}
}
