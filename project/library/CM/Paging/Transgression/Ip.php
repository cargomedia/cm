<?php

class CM_Paging_Transgression_Ip extends CM_Paging_Transgression_Abstract
 {
	
	private $_ip;
	
	/**
	 * @param int $ip
	 * @param int $modelType OPTIONAL
	 * @param int $actionType OPTIONAL
	 * @param int $limitType OPTIONAL
	 * @param int $period OPTIONAL
	 */
	public function __construct($ip = null, $modelType = null, $actionType = null, $limitType = null, $period = null) {
		if ($ip) {
			$this->_ip = (int) $ip;
			$where = '`ip` = ' . $this->_ip;
		} else {
			$where = '`ip` IS NOT NULL';
		}
		if ($modelType) {
			$modelType = (int) $modelType;
			$where .= ' AND `modelType` = ' . $modelType;
		}
		if ($actionType) {
			$actionType = (int) $actionType;
			$where .= ' AND `actionType` = ' . $actionType;
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
		$source = new CM_PagingSource_Sql_Deferred('modelType, actionType, createStamp', TBL_CM_ACTION, $where, '`createStamp` DESC');
		parent::__construct($source);
	}
	
	public function add(CM_Action_Abstract $action, $limitType) {
		$limitType = (int) $limitType;
		CM_Mysql::insertDelayed(TBL_CM_ACTION,
				array('ip' => $this->_ip, 'actionType' => $action->getType(), 'modelType' => $action->getModelType(), 'actionLimitType' => $limitType, 'createStamp' => time()));
	}
}
