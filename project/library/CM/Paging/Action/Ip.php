<?php

class CM_Paging_Action_Ip extends CM_Paging_Action_Abstract {
	
	private $_ip;
	
	/**
	 * @param int $ip
	 * @param int $modelType OPTIONAL
	 * @param int $actionType OPTIONAL
	 * @param int $period OPTIONAL
	 */
	public function __construct($ip, $modelType = null, $actionType = null, $period = null) {
		$this->_ip = (int) $ip;
		$period = (int) $period;
		$where = 'ip=' . $this->_ip . ' AND `actionLimitType` IS NULL';
		if ($modelType) {
			$modelType = (int) $modelType;
			$where .= ' AND `modelType` = ' . $modelType;
		}
		if ($actionType) {
			$actionType = (int) $actionType;
			$where .= ' AND `actionType` = ' . $actionType;
		}
		if ($period) {
			$time = time() - $period;
			$where .= ' AND `createStamp` > ' . $time;
		}
		$source = new CM_PagingSource_Sql_Deferred('modelType, actionType, createStamp', TBL_CM_ACTION, $where, '`createStamp` DESC');
		parent::__construct($source);
	}
	
	public function add(CM_Action_Abstract $action) {
		CM_Mysql::insertDelayed(TBL_CM_ACTION,
				array('ip' => $this->_ip, 'actionType' => $action->getType(), 'modelType' => $action->getModelType(), 'createStamp' => time()));
	}
}
