<?php

abstract class CM_Action_Abstract extends CM_Class_Abstract implements CM_ArrayConvertible {

	/**
	 * @var CM_Model_User
	 */
	protected $_actor = null;

	/**
	 * @var int
	 */
	protected $_type;

	/**
	 * @var int|null
	 */
	protected $_ip = null;

	/**
	 * @var array
	 */
	protected $_ignoreLogging = array();

	/**
	 * @param int			   $type
	 * @param CM_Model_User|int $actor
	 */
	public final function __construct($type, $actor) {
		if ($actor instanceof CM_Model_User) {
			$this->_actor = $actor;
		} elseif (ctype_digit($actor)) {
			$this->_ip = $actor;
		} else {
			throw new CM_Exception_Invalid('Actor must be of type `CM_Model_User` or `int`');
		}
		$this->_type = (int) $type;
	}

	/**
	 * @return int
	 */
	abstract public function getEntityType();

	/**
	 * @param CM_Model_Entity_Abstract $entity
	 * @param array					$data OPTIONAL
	 * @throws CM_Exception_Invalid
	 */
	abstract protected function _notify(CM_Model_Entity_Abstract $entity, array $data = null);

	abstract protected function _prepare();

	/**
	 * @param CM_Model_Entity_Abstract $entity
	 * @param array|null			   $data
	 */
	public final function notify(CM_Model_Entity_Abstract $entity, array $data = null) {
		$this->_notify($entity, $data);
	}

	public final function prepare() {
		$actionLimit = $this->getActionLimit($role);
		if ($actionLimit) {
			$isFirst = $this->_isFirstActionLimit($actionLimit, $role);
			$this->_log($actionLimit, $role);
			$actionLimit->overshoot($this, $role, $isFirst);
		} else {
			$this->_log();
		}
		$this->_prepare();
	}

	/**
	 * @param int $bestRole OPTIONAL reference for storing role associated with limit
	 * @return CM_Model_ActionLimit_Abstract|null
	 */
	public final function getActionLimit(&$bestRole = null) {
		foreach (new CM_Paging_ActionLimit_Action($this) as $actionLimit) {
			$bestRole = null;
			if ($this->getActor()) {
				$bestLimit = 0;
				foreach ($this->getActor()->getRoles()->get() as $role) {
					$limit = $actionLimit->getLimit($role);
					if ($limit === null || ($bestLimit !== null && $limit >= $bestLimit)) {
						$bestRole = $role;
						$bestLimit = $limit;
					}
				}
			}
			$limit = $actionLimit->getLimit($bestRole);
			if ($limit !== null &&
					($limit == 0 || $limit <= $this->_getSiblings($actionLimit->getPeriod($bestRole))->getCount())
			) {
				return $actionLimit;
			}
		}
		$bestRole = null;
		return null;
	}

	/**
	 * @return CM_Model_User|null
	 */
	public final function getActor() {
		return $this->_actor;
	}

	/**
	 * @return int|null
	 */
	public final function getIp() {
		return $this->_ip;
	}

	/**
	 * @return int
	 */
	public final function getType() {
		return $this->_type;
	}

	/**
	 * @return array
	 */
	public final function toArray() {
		return array('actor' => $this->getActor(), 'type' => $this->getType(), 'entityType' => $this->getEntityType());
	}

	/**
	 * @param CM_Model_ActionLimit_Abstract	 $actionLimit
	 * @param int                               $role
	 * @return bool
	 */
	private final function _isFirstActionLimit(CM_Model_ActionLimit_Abstract $actionLimit, $role) {
		$first = true;
		if ($actionLimit->getLimit($role)) {
			$transgressions = $this->_getTransgressions($actionLimit->getType(), $actionLimit->getPeriod($role));
			if ($transgressions->getCount()) {
				$actions = $this->_getSiblings($actionLimit->getPeriod($role));
				if ($actions->getCount()) {
					$lastAction = $actions->getItem(0);
					$lastTransgression = $transgressions->getItem(0);
					if ($lastAction['createStamp'] <= $lastTransgression['createStamp']) {
						$first = false;
					}
				} else {
					$first = false;
				}
			}
		}
		return $first;
	}

	/**
	 * @param int $within OPTIONAL
	 * @return CM_Paging_Action_Ip|CM_Paging_Action_User
	 * @throws CM_Exception_Invalid
	 */
	private final function _getSiblings($within = null) {
		if (in_array($this->getType(), $this->_ignoreLogging)) {
			throw new CM_Exception_Invalid(
				'Looking for actions of type `' . $this->getType() . '` on entityType `' . $this->getEntityType() .
						'` that is not being logged.');
		}
		if ($this->getActor()) {
			return $this->getActor()->getActions($this->getEntityType(), $this->getType(), $within);
		} else {
			return new CM_Paging_Action_Ip($this->getIp(), $this->getEntityType(), $this->getType(), $within);
		}
	}

	/**
	 * @param int $actionLimitType OPTIONAL
	 * @param int $period		  OPTIONAL
	 * @return CM_Paging_Transgression_Ip|CM_Paging_Transgression_User
	 * @throws CM_Exception_Invalid
	 */
	private final function _getTransgressions($actionLimitType = null, $period = null) {
		if (in_array($this->getType(), $this->_ignoreLogging)) {
			throw new CM_Exception_Invalid(
				'Looking for transgressions of type `' . $this->getType() . '` on entityType `' .
						$this->getEntityType() . '` that is not being logged.');
		}
		if ($this->getActor()) {
			return $this->getActor()->getTransgressions($this->getEntityType(), $this->getType(), $actionLimitType, $period);
		} else {
			return new CM_Paging_Transgression_Ip($this->getIp(), $this->getEntityType(), $this->getType(), $actionLimitType, $period);
		}
	}

	/**
	 * @param CM_Model_ActionLimit_Abstract $actionLimit OPTIONAL
	 * @param int						   $role		OPTIONAL
	 */
	private final function _log(CM_Model_ActionLimit_Abstract $actionLimit = null, $role = null) {
		if (!in_array($this->getType(), $this->_ignoreLogging)) {
			if ($actionLimit) {
				$this->_getTransgressions()->add($this, $actionLimit->getType(), $actionLimit->getPeriod($role));
			} else {
				$this->_getSiblings()->add($this);
			}
		}
	}

	/**
	 * @param int $age Seconds
	 */
	public static final function deleteOlder($age) {
		$age = (int) $age;
		CM_Mysql::exec("DELETE FROM TBL_ACTION WHERE `createStamp` < ?", time() - $age);
	}

	/**
	 * @param array $data
	 */
	public static function fromArray(array $data) {
		throw new CM_Exception_NotImplemented();
	}

	/**
	 * @param CM_Model_User $actor
	 * @param int		   $type
	 * @param int		   $entityType
	 *
	 * @return CM_Action_Abstract
	 * @throws CM_Exception
	 */
	public static function factory(CM_Model_User $actor, $type, $entityType) {
		$class = self::_getClassName($entityType);
		return new $class($type, $actor);
	}
}
