<?php

abstract class CM_Model_ActionLimit_Abstract extends CM_Model_Abstract {

	/**
	 * @var array
	 */
	public $_data;

	/**
	 * @param int $actionType
	 * @param int $actionVerb
	 */
	public function __construct($actionType, $actionVerb) {
		parent::_construct(array('actionType' => (int) $actionType, 'actionVerb' => (int) $actionVerb));
	}

	/**
	 * @param CM_Action_Abstract $action
	 * @param int|null           $role
	 * @param boolean            $first
	 */
	abstract public function overshoot(CM_Action_Abstract $action, $role, $first);

	/**
	 * @param int|null $role
	 * @param int|null $limit
	 */
	public function setLimit($role, $limit) {
		$role = $role ? (int) $role : null;
		$limit = !isset($limit) ? null : (int) $limit;
		if (CM_Mysql::count(TBL_CM_ACTIONLIMIT, array('actionType' => $this->getActionType(), 'actionVerb' => $this->getActionVerb(),
			'type' => $this->getType(), 'role' => $role))
		) {
			CM_Mysql::update(TBL_CM_ACTIONLIMIT, array('limit' => $limit), array('actionType' => $this->getActionType(),
				'actionVerb' => $this->getActionVerb(), 'type' => $this->getType(), 'role' => $role));
		} else {
			CM_Mysql::insert(TBL_CM_ACTIONLIMIT, array('limit' => $limit, 'actionType' => $this->getActionType(),
				'actionVerb' => $this->getActionVerb(), 'type' => $this->getType(), 'role' => $role));
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
		if (CM_Mysql::count(TBL_CM_ACTIONLIMIT, array('actionType' => $this->getActionType(), 'actionVerb' => $this->getActionVerb(),
			'type' => $this->getType(), 'role' => $role))
		) {
			CM_Mysql::update(TBL_CM_ACTIONLIMIT, array('period' => $period), array('actionType' => $this->getActionType(),
				'actionVerb' => $this->getActionVerb(), 'type' => $this->getType(), 'role' => $role));
		} else {
			CM_Mysql::insert(TBL_CM_ACTIONLIMIT, array('period' => $period, 'actionType' => $this->getActionType(),
				'actionVerb' => $this->getActionVerb(), 'type' => $this->getType(), 'role' => $role));
		}
		$this->_change();
	}

	/**
	 * @return int
	 */
	public function getActionVerb() {
		$id = $this->_getId();
		return (int) $id['actionVerb'];
	}

	/**
	 * @return int
	 */
	public function getActionType() {
		$id = $this->_getId();
		return (int) $id['actionType'];
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
		return array('roles' => CM_Mysql::select(TBL_CM_ACTIONLIMIT, array('role', 'limit', 'period'), array('actionType' => $this->getActionType(),
			'actionVerb' => $this->getActionVerb(), 'type' => $this->getType()))->fetchAllTree());
	}

	/**
	 * @param int $type
	 * @param int $actionType
	 * @param int $actionVerb
	 * @return CM_Model_ActionLimit_Abstract
	 */
	public static function factory($type, $actionType, $actionVerb) {
		$class = self::_getClassName($type);
		return new $class($actionType, $actionVerb);
	}

	/**
	 * @param int $type OPTIONAL
	 * @return CM_Paging_ActionLimit_All
	 */
	public static function getAll($type = null) {
		if (!$type) {
			$type = static::TYPE;
		}
		return new CM_Paging_ActionLimit_All($type);
	}
}
