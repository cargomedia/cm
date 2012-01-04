<?php

abstract class CM_Model_ActionLimit_Abstract extends CM_Model_Abstract {
	
	/**
	 * @var array
	 */
	public $_data;
	
	/**
	 * @param int $entityType
	 * @param int $actionType
	 */
	public function __construct($entityType, $actionType) {
		parent::_construct(array('entityType' => (int) $entityType, 'actionType' => (int) $actionType));
	}
	
	/**
	 * @param CM_Action_Abstract $action
	 * @param int|null $role
	 * @param boolean $first
	 */
	abstract public function overshoot(CM_Action_Abstract $action, $role, $first);

	/**
	 * @param int|null $role
	 * @param int|null $limit
	 */
	public function setLimit($role, $limit) {
		$role = $role ? (int) $role : null;
		$limit = !isset($limit) ? null : (int) $limit;
		if (CM_Mysql::count(TBL_CM_ACTIONLIMIT, array('entityType' => $this->getEntityType(), 'actionType' => $this->getActionType(), 'type' => $this->getType(), 'role' => $role))) {
			CM_Mysql::update(TBL_CM_ACTIONLIMIT, array('limit' => $limit), array('entityType' => $this->getEntityType(), 'actionType' => $this->getActionType(), 'type' => $this->getType(), 'role' => $role));
		} else {
			CM_Mysql::insert(TBL_CM_ACTIONLIMIT, array('limit' => $limit, 'entityType' => $this->getEntityType(), 'actionType' => $this->getActionType(), 'type' => $this->getType(), 'role' => $role));
		}
		$this->_change();
	}

	/**
	 * @param int|null $role
	 * @param int|null $period
	 */
	public function setPeriod($role, $period) {
		$role = $role ? (int) $role : null;
		$period = (int) $period;
		if (CM_Mysql::count(TBL_CM_ACTIONLIMIT, array('entityType' => $this->getEntityType(), 'actionType' => $this->getActionType(), 'type' => $this->getType(), 'role' => $role))) {
			CM_Mysql::update(TBL_CM_ACTIONLIMIT, array('period' => $period), array('entityType' => $this->getEntityType(), 'actionType' => $this->getActionType(), 'type' => $this->getType(), 'role' => $role));
		} else {
			CM_Mysql::insert(TBL_CM_ACTIONLIMIT, array('period' => $period, 'entityType' => $this->getEntityType(), 'actionType' => $this->getActionType(), 'type' => $this->getType(), 'role' => $role));
		}
		$this->_change();
	}

	/**
	 * @return int
	 */
	public function getActionType() {
		$id = $this->_getId();
		return (int) $id['actionType'];
	}
	
	/**
	 * @return int
	 */
	public function getEntityType() {
		$id = $this->_getId();
		return (int) $id['entityType'];
	}
	
	/**
	 * @param int $role OPTIONAL
	 * 
	 * @return int|null
	 */
	public function getLimit($role = null) {
		$roles = $this->_get('roles');
		if (!isset($roles[$role])) {
			if ($role === null) {
				return null;
			}
			return $this->getLimit();
		}
		return $roles[$role]['limit'];
	}

	/**
	 * @param int $role OPTIONAL
	 * 
	 * @return int|null
	 */
	public function getPeriod($role = null) {
		$roles = $this->_get('roles');
		if (!isset($roles[$role])) {
			if ($role === null) {
				return null;
			}
			return $this->getPeriod();
		}
		return $roles[$role]['period'];
	}

	protected function _loadData() {
		return array('roles' => CM_Mysql::select(TBL_CM_ACTIONLIMIT, array('role', 'limit', 'period'), array('entityType' => $this->getEntityType(), 'actionType' => $this->getActionType(), 'type' => $this->getType()))->fetchAllTree());
    }

	protected function _onChange() {
	}

	protected function _onDelete() {
	}

	protected function _onLoad() {
	}

	/**
	 * @param int $type
	 * @param int $entityType
	 * @param int $actionType
	 * @return CM_ActionLimit_Abstract
	 */
	public static function factory($type, $entityType, $actionType) {
		$class = self::_getClassName($type);
		return new $class($entityType, $actionType);
	}

	/**
	 * @param int $type OPTIONAL
	 * @return CM_Paging_ActionLimit_All
	 */
	public static function getAll($type = null) {
		if (!$type) {
			$type = static::getType();
		}
		return new CM_Paging_ActionLimit_All($type);
	}
}
