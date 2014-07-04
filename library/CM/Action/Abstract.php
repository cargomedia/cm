<?php

abstract class CM_Action_Abstract extends CM_Class_Abstract implements CM_ArrayConvertible, CM_Typed {

    const CREATE = 'CREATE';
    const UPDATE = 'UPDATE';
    const DELETE = 'DELETE';
    const ONLINE = 'ONLINE';
    const OFFLINE = 'OFFLINE';
    const VIEW = 'VIEW';
    const VISIBLE = 'VISIBLE';
    const INVISIBLE = 'INVISIBLE';
    const PUBLISH = 'PUBLISH';
    const UNPUBLISH = 'UNPUBLISH';
    const SUBSCRIBE = 'SUBSCRIBE';
    const UNSUBSCRIBE = 'UNSUBSCRIBE';

    /** @var CM_Model_User|int */
    protected $_actor = null;

    /** @var int */
    protected $_verb;

    /** @var int|null */
    protected $_ip = null;

    /** @var bool */
    private $_actionLimitsEnabled = true;

    /** @var bool */
    private $_trackingEnabled = true;

    /** @var array */
    private $_trackingPropertyList = array();

    /**
     * @param string            $verbName
     * @param CM_Model_User|int $actor
     */
    public function __construct($verbName, $actor) {
        $this->setActor($actor);
        $this->_verb = CM_Action_Abstract::getVerbByVerbName($verbName);
    }

    protected function _notify() {
        $arguments = func_get_args();
        $methodName = '_notify' . CM_Util::camelize($this->getVerbName());

        if (method_exists($this, $methodName)) {
            call_user_func_array(array($this, $methodName), $arguments);
        }

        $this->_track();
    }

    /**
     * @return bool
     */
    protected function _isAllowed() {
        $arguments = func_get_args();
        $methodName = '_isAllowed' . CM_Util::camelize($this->getVerbName());

        if (method_exists($this, $methodName)) {
            return call_user_func_array(array($this, $methodName), $arguments);
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isAllowed() {
        $arguments = func_get_args();
        if (!call_user_func_array(array($this, '_isAllowed'), $arguments)) {
            return false;
        }
        $actionLimitList = $this->getActionLimitsTransgressed();
        foreach ($actionLimitList as $actionLimitData) {
            /** @var CM_Model_ActionLimit_Abstract $actionLimit */
            $actionLimit = $actionLimitData['actionLimit'];
            if (!$actionLimit->getOvershootAllowed()) {
                return false;
            }
        }
        return true;
    }

    abstract protected function _prepare();

    public function prepare() {
        $arguments = func_get_args();
        if (!call_user_func_array(array($this, '_isAllowed'), $arguments)) {
            throw new CM_Exception_NotAllowed('Action not allowed', null, array('messagePublic' => 'The content you tried to interact with has become private.'));
        }
        $this->_checkActionLimits();
        $this->_prepare();
    }

    /**
     * @return array ['actionLimit' => CM_Model_ActionLimit_Abstract, 'role' => int]
     */
    public function getActionLimitsTransgressed() {
        $actionLimitsTransgressed = array();
        /** @var CM_Model_ActionLimit_Abstract $actionLimit */
        foreach ($this->_getActionLimitList() as $actionLimit) {
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
            if ($limit !== null && ($limit == 0 || $limit <= $this->_getSiblings($actionLimit->getPeriod($bestRole))->getCount())) {
                $actionLimitsTransgressed[] = array('actionLimit' => $actionLimit, 'role' => $bestRole);
            }
        }
        return $actionLimitsTransgressed;
    }

    /**
     * @return CM_Model_User|null
     */
    public function getActor() {
        return $this->_actor;
    }

    /**
     * @param CM_Model_User|int $actor
     * @throws CM_Exception_Invalid
     */
    public function setActor($actor) {
        if ($actor instanceof CM_Model_User) {
            $this->_actor = $actor;
            $this->_ip = null;
        } elseif (is_int($actor) || ctype_digit($actor)) {
            $this->_actor = null;
            $this->_ip = $actor;
        } else {
            throw new CM_Exception_Invalid('Actor must be of type `CM_Model_User` or `int`');
        }
    }

    /**
     * @return int|null
     */
    public function getIp() {
        return $this->_ip;
    }

    /**
     * @return array
     */
    public function getTrackingPropertyList() {
        return $this->_trackingPropertyList;
    }

    /**
     * @return int
     */
    public function getVerb() {
        return $this->_verb;
    }

    /**
     * @return CM_Paging_ActionLimit_Action
     */
    protected function _getActionLimitList() {
        return new CM_Paging_ActionLimit_Action($this);
    }

    private function _checkActionLimits() {
        if (!$this->_actionLimitsEnabled) {
            return;
        }
        $role = null;
        $actionLimitList = $this->getActionLimitsTransgressed();
        foreach ($actionLimitList as $actionLimitData) {
            /** @var CM_Model_ActionLimit_Abstract $actionLimit */
            $actionLimit = $actionLimitData['actionLimit'];
            $role = $actionLimitData['role'];
            $isFirst = $this->_isFirstActionLimit($actionLimit, $role);
            if ($isFirst) {
                $this->_log($actionLimit);
            }
            $actionLimit->overshoot($this, $role, $isFirst);
            if (!$actionLimit->getOvershootAllowed()) {
                throw new CM_Exception_NotAllowed('ActionLimit `' . $actionLimit->getType() . '` breached.');
            }
        }
        $this->_log();
    }

    /**
     * @param CM_Model_ActionLimit_Abstract $actionLimit
     * @param int                           $role
     * @return bool
     */
    private function _isFirstActionLimit(CM_Model_ActionLimit_Abstract $actionLimit, $role) {
        $first = true;
        if (null !== $actionLimit->getLimit($role) && 0 !== $actionLimit->getLimit($role)) {
            $period = $actionLimit->getPeriod($role);
            $transgressions = $this->_getTransgressions($actionLimit->getType(), $period);
            if ($transgressions->getCount()) {
                $lastTransgression = $transgressions->getItem(0);
                $actions = $this->_getSiblings($period, $lastTransgression['createStamp']);
                if ($actions->getCount()) {
                    $firstAction = $actions->getItem(-1);
                    if (time() < ($firstAction['createStamp'] + $period)) {
                        $first = false;
                    }
                }
            }
        }
        return $first;
    }

    /**
     * @param int|null $within
     * @param int|null $upperBound
     * @return CM_Paging_Action_Ip|CM_Paging_Action_User
     */
    private function _getSiblings($within = null, $upperBound = null) {
        if ($this->getActor()) {
            return $this->getActor()->getActions($this->getType(), $this->getVerb(), $within, $upperBound);
        } else {
            return new CM_Paging_Action_Ip($this->getIp(), $this->getType(), $this->getVerb(), $within, $upperBound);
        }
    }

    /**
     * @param int $actionLimitType OPTIONAL
     * @param int $period          OPTIONAL
     * @return CM_Paging_Transgression_Ip|CM_Paging_Transgression_User
     */
    private function _getTransgressions($actionLimitType = null, $period = null) {
        if ($this->getActor()) {
            return $this->getActor()->getTransgressions($this->getType(), $this->getVerb(), $actionLimitType, $period);
        } else {
            return new CM_Paging_Transgression_Ip($this->getIp(), $this->getType(), $this->getVerb(), $actionLimitType, $period);
        }
    }

    /**
     * @param CM_Model_ActionLimit_Abstract|null $actionLimit
     */
    private function _log(CM_Model_ActionLimit_Abstract $actionLimit = null) {
        if ($actionLimit) {
            $this->_getTransgressions()->add($this, $actionLimit->getType());
        } else {
            $this->_getSiblings()->add($this);
        }
    }

    /**
     * @param int $age Seconds
     */
    public static function deleteOlder($age) {
        $age = (int) $age;
        CM_Db_Db::delete('cm_action', '`createStamp` < ' . (time() - $age));
    }

    public function toArray() {
        return array('actor' => $this->getActor(), 'verb' => $this->getVerb(), 'type' => $this->getType());
    }

    public static function fromArray(array $data) {
        $verb = CM_Action_Abstract::getVerbNameByVerb($data['verb']);
        return self::factory($data['actor'], $verb, $data['type']);
    }

    /**
     * @param CM_Model_User $actor
     * @param string        $verbName
     * @param int           $type
     *
     * @return CM_Action_Abstract
     * @throws CM_Exception
     */
    public static function factory(CM_Model_User $actor, $verbName, $type) {
        $class = self::_getClassName($type);
        return new $class($verbName, $actor);
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
            $timeMin = CM_Db_Db::exec('SELECT MIN(`createStamp`) FROM `cm_action` WHERE `actionLimitType` IS NULL AND `interval` < ?', array($interval['interval']))->fetchColumn();
            if (null === $timeMin) {
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
        $result = CM_Db_Db::exec(
            "SELECT `verb`, `type`, COUNT(*) AS `count`, SUM(`count`) AS `sum` FROM `cm_action` WHERE " . $where . " GROUP BY `verb`, `type`");
        $insert = array();
        while ($row = $result->fetch()) {
            if ($row['count'] >= 1) {
                $insert[] = array((int) $row['verb'], (int) $row['type'], $timeStamp, (int) $row['sum'], ($upperBound - $lowerBound));
            }
        }
        if (!empty($insert)) {
            CM_Db_Db::delete('cm_action', $where);
            CM_Db_Db::insert('cm_action', array('verb', 'type', 'createStamp', 'count', 'interval'), $insert);
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
        return self::getNameByClassName($this->_getClassName());
    }

    /**
     * @return string
     */
    public function getLabel() {
        $actionName = CM_Util::uncamelize(str_replace('_', '', preg_replace('#\\A[^_]++_[^_]++_#', '', get_called_class())), ' ');
        $verbName = strtolower(str_replace('_', ' ', $this->getVerbName()));
        return ucwords($actionName . ' ' . $verbName);
    }

    protected function _disableTracking() {
        $this->_trackingEnabled = false;
    }

    /**
     * @param array $trackingPropertyList
     */
    protected function _setTrackingPropertyList(array $trackingPropertyList) {
        $this->_trackingPropertyList = $trackingPropertyList;
    }

    protected function _track() {
        if ($this->_trackingEnabled) {
            CM_Service_Manager::getInstance()->getTrackings()->trackAction($this);
        }
    }

    /**
     * @param int $type
     * @return string
     */
    static public function getNameByType($type) {
        $className = self::_getClassName($type);
        return self::getNameByClassName($className);
    }

    /**
     * @param string $className
     * @return string
     */
    static public function getNameByClassName($className) {
        return str_replace('_', ' ', str_replace(CM_Util::getNamespace($className) . '_Action_', '', $className));
    }

    /**
     * @param int $verb
     * @return string
     * @throws CM_Exception_Invalid
     */
    static public function getVerbNameByVerb($verb) {
        $actionVerbNames = array_flip(self::_getConfig()->verbs);
        if (!array_key_exists($verb, $actionVerbNames)) {
            throw new CM_Exception_Invalid('The specified Action does not exist!');
        }
        return $actionVerbNames[$verb];
    }

    /**
     * @param string $name
     * @return int
     * @throws CM_Exception_Invalid
     */
    static public function getVerbByVerbName($name) {
        $actionVerbs = self::_getConfig()->verbs;
        if (!array_key_exists($name, $actionVerbs)) {
            throw new CM_Exception_Invalid('Action `' . $name . '` does not exist!');
        }
        return $actionVerbs[$name];
    }
}
