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
	 * @return int
	 */
	public function getActionType() {
		$id = $this->_getId();
		return (int) $id['actionType'];
	}

	/**
	 * @return int
	 */
	public function getActionVerb() {
		$id = $this->_getId();
		return (int) $id['actionVerb'];
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
	 * @param int|null $role
	 * @param int|null $limit
	 */
	public function setLimit($role, $limit) {
		$role = $role ? (int) $role : null;
		$limit = !isset($limit) ? null : (int) $limit;
		if (CM_Db_Db::count(TBL_CM_ACTIONLIMIT, array('actionType' => $this->getActionType(), 'actionVerb' => $this->getActionVerb(),
			'type' => $this->getType(), 'role' => $role))
		) {
			CM_Db_Db::update(TBL_CM_ACTIONLIMIT, array('limit' => $limit), array('actionType' => $this->getActionType(),
				'actionVerb' => $this->getActionVerb(), 'type' => $this->getType(), 'role' => $role));
		} else {
			CM_Mysql::insert(TBL_CM_ACTIONLIMIT, array('limit' => $limit, 'actionType' => $this->getActionType(),
				'actionVerb' => $this->getActionVerb(), 'type' => $this->getType(), 'role' => $role));
		}
		$this->_change();
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
		return (int) $roles[$role]['period'];
	}

	/**
	 * @param int|null $role
	 * @param int      $period
	 */
	public function setPeriod($role, $period) {
		$role = $role ? (int) $role : null;
		$period = (int) $period;
		if (CM_Db_Db::count(TBL_CM_ACTIONLIMIT, array('actionType' => $this->getActionType(), 'actionVerb' => $this->getActionVerb(),
			'type' => $this->getType(), 'role' => $role))
		) {
			CM_Db_Db::update(TBL_CM_ACTIONLIMIT, array('period' => $period), array('actionType' => $this->getActionType(),
				'actionVerb' => $this->getActionVerb(), 'type' => $this->getType(), 'role' => $role));
		} else {
			CM_Mysql::insert(TBL_CM_ACTIONLIMIT, array('period' => $period, 'actionType' => $this->getActionType(),
				'actionVerb' => $this->getActionVerb(), 'type' => $this->getType(), 'role' => $role));
		}
		$this->_change();
	}

	/**
	 * @param null|int $role
	 * @return boolean
	 */
	public function hasLimit($role = null) {
		$role = $role ? (int) $role : null;
		$roles = $this->_get('roles');
		return isset($roles[$role]);
	}

	/**
	 * @param int|null $role
	 */
	public function unsetLimit($role = null) {
		$role = $role ? (int) $role: null;
		CM_Mysql::delete(TBL_CM_ACTIONLIMIT, array('actionType' => $this->getActionType(), 'actionVerb' => $this->getActionVerb(), 'type' => $this->getType(), 'role' => $role));
		$this->_change();
	}

	protected function _loadData() {
		return array('roles' => CM_Db_Db::select(TBL_CM_ACTIONLIMIT, array('role', 'limit', 'period'), array('actionType' => $this->getActionType(),
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
