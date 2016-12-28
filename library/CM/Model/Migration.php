<?php

class CM_Model_Migration extends CM_Model_Abstract {

    /**
     * @return string
     */
    public function getName() {
        return $this->_get('name');
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name) {
        $this->_set('name', $name);
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getExecStamp() {
        return $this->_has('execStamp') ? $this->_get('execStamp') : null;
    }

    /**
     * @param DateTime $execStamp
     * @return $this
     */
    public function setExecStamp($execStamp) {
        $this->_set('execStamp', $execStamp);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasExecStamp() {
        return null !== $this->getExecStamp();
    }

    protected function _getSchema() {
        return new CM_Model_Schema_Definition(array(
            'name'      => ['type' => 'string'],
            'execStamp' => ['type' => 'DateTime', 'optional' => true],
        ));
    }

    /**
     * @param string $name
     * @return CM_Model_Migration
     */
    static public function create($name) {
        $model = new self();
        $model->setName($name);
        $model->commit();
        return $model;
    }

    /**
     * @param string $name
     * @return CM_Model_Migration
     */
    static public function findByName($name) {
        $cache = CM_Cache_Local::getInstance();
        if (false === ($migrationId = $cache->get($name))) {
            $migrationId = CM_Db_Db::select('cm_model_migration', 'id', array('name' => $name))->fetchColumn();
            $cache->set($name, $migrationId);
        }
        if (!$migrationId) {
            return null;
        }
        return new static($migrationId);
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_Database';
    }
}
