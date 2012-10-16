<?php

abstract class CM_Action_Abstract extends CM_Class_Abstract implements CM_ArrayConvertible {

	/**
	 * @var CM_Model_User|int
	 */
	protected $_actor = null;

	/**
	 * @var int
	 */
	protected $_verb;

	/**
	 * @var int|null
	 */
	protected $_ip = null;

	/**
	 * @var array
	 */
	protected $_ignoreLogging = array();

	/**
	 * @var bool
	 */
	private $_forceAllow = false;

	/**
	 * @param int			   $verb
	 * @param CM_Model_User|int $actor
	 */
	public final function __construct($verb, $actor) {
		if ($actor instanceof CM_Model_User) {
			$this->_actor = $actor;
		} elseif (is_int($actor) || ctype_digit($actor)) {
			$this->_ip = $actor;
		} else {
			throw new CM_Exception_Invalid('Actor must be of type `CM_Model_User` or `int`');
		}
		$this->_verb = (int) $verb;
	}

	abstract protected function _notify();

	abstract protected function _prepare();

	public final function prepare() {
		$role = null;
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
	 * @param bool $forceAllow
	 */
	public function forceAllow($forceAllow) {
		$this->_forceAllow = (bool) $forceAllow;
	}

	/**
	 * @param int &$bestRole OPTIONAL reference for storing role associated with limit
	 * @return CM_Model_ActionLimit_Abstract|null
	 */
	public final function getActionLimit(&$bestRole = null) {
		if ($this->_forceAllow) {
			return null;
		}
		/** @var CM_Model_ActionLimit_Abstract $actionLimit */
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
			if ($limit !== null && ($limit == 0 || $limit <= $this->_getSiblings($actionLimit->getPeriod($bestRole))->getCount())
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
	public final function getVerb() {
		return $this->_verb;
	}

	/**
	 * @param CM_Model_ActionLimit_Abstract	 $actionLimit
	 * @param int							   $role
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
		if (in_array($this->getVerb(), $this->_ignoreLogging)) {
			throw new CM_Exception_Invalid(
				'Looking for actions of verb `' . $this->getVerb() . '` on actionType `' . $this->getType() . '` that is not being logged.');
		}
		if ($this->getActor()) {
			return $this->getActor()->getActions($this->getType(), $this->getVerb(), $within);
		} else {
			return new CM_Paging_Action_Ip($this->getIp(), $this->getType(), $this->getVerb(), $within);
		}
	}

	/**
	 * @param int $actionLimitType OPTIONAL
	 * @param int $period		  OPTIONAL
	 * @return CM_Paging_Transgression_Ip|CM_Paging_Transgression_User
	 * @throws CM_Exception_Invalid
	 */
	private final function _getTransgressions($actionLimitType = null, $period = null) {
		if (in_array($this->getVerb(), $this->_ignoreLogging)) {
			throw new CM_Exception_Invalid('Looking for transgressions of verb `' . $this->getVerb() . '` on actionType `' . $this->getType() .
					'` that is not being logged.');
		}
		if ($this->getActor()) {
			return $this->getActor()->getTransgressions($this->getType(), $this->getVerb(), $actionLimitType, $period);
		} else {
			return new CM_Paging_Transgression_Ip($this->getIp(), $this->getType(), $this->getVerb(), $actionLimitType, $period);
		}
	}

	/**
	 * @param CM_Model_ActionLimit_Abstract $actionLimit OPTIONAL
	 * @param int						   $role		OPTIONAL
	 */
	private final function _log(CM_Model_ActionLimit_Abstract $actionLimit = null, $role = null) {
		if (!in_array($this->getVerb(), $this->_ignoreLogging)) {
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
		CM_Mysql::exec("DELETE FROM TBL_CM_ACTION WHERE `createStamp` < ?", time() - $age);
	}

	public final function toArray() {
		return array('actor' => $this->getActor(), 'verb' => $this->getVerb(), 'type' => $this->getType());
	}

	public static function fromArray(array $data) {
		return self::factory($data['actor'], $data['verb'], $data['type']);
	}

	/**
	 * @param CM_Model_User $actor
	 * @param int		   $verb
	 * @param int		   $type
	 *
	 * @return CM_Action_Abstract
	 * @throws CM_Exception
	 */
	public static function factory(CM_Model_User $actor, $verb, $type) {
		$class = self::_getClassName($type);
		return new $class($verb, $actor);
	}

	/**
	 * @param array|null $intervals
	 */
	public static function aggregate(array $intervals = null) {
		if (is_null($intervals)) {
			$intervals = array(array('limit' => 86400, 'interval' => 3600), array('limit' => 7 * 86400, 'interval' => 86400));
		}
		$intervalValueLast = 1;
		foreach ($intervals as &$intervalRef) {
			if (!empty($intervalRef['interval'])) {
				if ($intervalRef['interval'] % $intervalValueLast !== 0) {
					throw new CM_Exception_Invalid('Interval `' . $intervalRef['interval'] . '` is not a multiple of `' . $intervalValueLast . '`.');
				}
				$intervalValueLast = $intervalRef['interval'];
			}
		}

		$time = time();
		foreach (array_reverse($intervals) as $interval) {
			$timeMin = CM_Mysql::exec('SELECT MIN(`createStamp`) FROM ' . TBL_CM_ACTION .
					' WHERE `actionLimitType` IS NULL AND `interval` < ?', $interval['interval'])->fetchOne();
			if (false === $timeMin) {
				return;
			}
			$timeMin -= $timeMin % $interval['interval'];
			$timeMax = $time - $interval['limit'];

			for ($timeCurrent = $timeMin; ($timeCurrent + $interval['interval']) <= $timeMax; $timeCurrent += $interval['interval']) {
				self::collapse($timeCurrent, $timeCurrent + $interval['interval']);
			}
		}
	}

	/**
	 * @param int $lowerBound
	 * @param int $upperBound
	 */
	public static function collapse($lowerBound, $upperBound) {
		$lowerBound = (int) $lowerBound;
		$upperBound = (int) $upperBound;
		$timeStamp = floor(($upperBound + $lowerBound) / 2);
		$where = '`createStamp` >= ' . $lowerBound . ' AND `createStamp` < ' . $upperBound . ' AND `actionLimitType` IS NULL';
		$result = CM_Mysql::exec("SELECT `verb`, `type`, COUNT(*) AS `count`, SUM(`count`) AS `sum` FROM TBL_CM_ACTION WHERE " . $where .
				" GROUP BY `verb`, `type`");
		$insert = array();
		while ($row = $result->fetchAssoc()) {
			if ($row['count'] >= 1) {
				$insert[] = array((int) $row['verb'], (int) $row['type'], $timeStamp, (int) $row['sum'], ($upperBound - $lowerBound));
			}
		}
		if (!empty($insert)) {
			CM_Mysql::delete(TBL_CM_ACTION, $where);
			CM_Mysql::insert(TBL_CM_ACTION, array('verb', 'type', 'createStamp', 'count', 'interval'), $insert);
		}
	}

	/**
	 * @return string
	 */
	public function getVerbName() {
		return self::getVerbNameByVerb($this->getVerb());
	}

	/**
	 * @return string
	 */
	public function getName() {
		return self::getNameByType($this->getType());
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->getName() . ' ' . $this->getVerbName();
	}

	/**
	 * @param int $type
	 * @return string
	 */
	static public function getNameByType($type) {
		$className = self::_getClassName($type);
		return str_replace('_', ' ', str_replace(self::_getNamespace($className) . '_Action_', '', $className));
	}

	/**
	 * @param int $verb
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	static public function getVerbNameByVerb($verb) {
		$actionVerbs = self::_getConfig()->verbs;
		if (!array_key_exists($verb, $actionVerbs)) {
			throw new CM_Exception_Invalid('The specified Action does not exist!');
		}
		return $actionVerbs[$verb];
	}
}
