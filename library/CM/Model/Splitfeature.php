<?php

class CM_Model_Splitfeature extends CM_Model_Abstract {

    /** @var bool */
    private $_withoutPersistence;

    /**
     * @param string $name
     */
    public function __construct($name) {
        $this->_withoutPersistence = !empty(self::_getConfig()->withoutPersistence);
        $this->_construct(array('name' => $name));
    }

    protected function _getContainingCacheables() {
        $cacheables = parent::_getContainingCacheables();
        $cacheables[] = new CM_Paging_Splitfeature_All();
        return $cacheables;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_getId('name');
    }

    public function getId() {
        return (int) $this->_get('id');
    }

    /**
     * @return int
     */
    public function getPercentage() {
        return (int) $this->_get('percentage');
    }

    /**
     * @param int $percentage
     */
    public function setPercentage($percentage) {
        if ($this->_withoutPersistence) {
            return;
        }
        $percentage = $this->_checkPercentage($percentage);

        CM_Db_Db::update('cm_splitfeature', array('percentage' => $percentage), array('id' => $this->getId()));
        $this->_change();
    }

    /**
     * @param CM_Model_User $user
     * @throws CM_Exception_Invalid
     * @return boolean
     */
    public function getEnabled(CM_Model_User $user) {
        if ($this->_withoutPersistence) {
            return false;
        }
        $cacheKey = CM_CacheConst::SplitFeature_Fixtures . '_userId:' . $user->getId();
        $cacheWrite = false;
        $cache = CM_Cache_Local::getInstance();
        if (($fixtures = $cache->get($cacheKey)) === false) {
            $fixtures = CM_Db_Db::select('cm_splitfeature_fixture', array('splitfeatureId',
                'fixtureId'), array('userId' => $user->getId()))->fetchAllTree();
            $cacheWrite = true;
        }

        if (!array_key_exists($this->getId(), $fixtures)) {
            $fixtureId = CM_Db_Db::replace('cm_splitfeature_fixture', array('splitfeatureId' => $this->getId(), 'userId' => $user->getId()));
            $fixtures[$this->getId()] = $fixtureId;
            $cacheWrite = true;
        }

        if ($cacheWrite) {
            $cache->set($cacheKey, $fixtures);
        }

        return $this->_calculateEnabled($fixtures[$this->getId()]);
    }

    /**
     * @return int
     */
    public function getFixtureCount() {
        if ($this->_withoutPersistence) {
            return 0;
        }
        return CM_Db_Db::count('cm_splitfeature_fixture', array('splitfeatureId' => $this->getId()));
    }

    /**
     * @param int $fixtureId
     * @return bool
     */
    protected function _calculateEnabled($fixtureId) {
        $fixtureIdInternal = $fixtureId - 1;
        return ($fixtureIdInternal % 100 < $this->getPercentage());
    }

    protected function _loadData() {
        if ($this->_withoutPersistence) {
            return array();
        }
        $data = CM_Db_Db::select('cm_splitfeature', '*', array('name' => $this->getName()))->fetch();
        return $data;
    }

    protected function _onDeleteBefore() {
        CM_Db_Db::delete('cm_splitfeature_fixture', array('splitfeatureId' => $this->getId()));
    }

    protected function _onDelete() {
        CM_Db_Db::delete('cm_splitfeature', array('id' => $this->getId()));
    }

    protected static function _createStatic(array $data) {
        $name = (string) $data['name'];
        $percentage = self::_checkPercentage($data['percentage']);

        CM_Db_Db::insert('cm_splitfeature', array('name' => $name, 'percentage' => $percentage));

        return new static($name);
    }

    /**
     * @param int $percentage
     * @return int
     * @throws CM_Exception_InvalidParam
     */
    private static function _checkPercentage($percentage) {
        $percentage = (int) $percentage;

        if ($percentage < 0 || $percentage > 100) {
            throw new CM_Exception_InvalidParam('Percentage must be between 0 and 100 ' . $percentage . ' was given');
        }

        return $percentage;
    }

    /**
     * @param string        $name
     * @param CM_Model_User $user
     * @return bool
     */
    public static function getEnabledByName($name, CM_Model_User $user) {
        $splitfeatureList = new CM_Paging_Splitfeature_All();
        $splitfeature = $splitfeatureList->find($name);
        if (!$splitfeature) {
            return false;
        }
        return $splitfeature->getEnabled($user);
    }

    /**
     * @param string $name
     * @return CM_Model_Splitfeature
     */
    public static function factory($name) {
        $className = self::_getClassName();
        return new $className($name);
    }
}
