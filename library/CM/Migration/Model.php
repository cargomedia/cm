<?php

class CM_Migration_Model extends CM_Model_Abstract {

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
    public function getExecutedAt() {
        return $this->_has('executedAt') ? $this->_get('executedAt') : null;
    }

    /**
     * @param DateTime|null $executedAt
     * @return $this
     */
    public function setExecutedAt(DateTime $executedAt = null) {
        $this->_set('executedAt', $executedAt);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasExecutedAt() {
        return null !== $this->getExecutedAt();
    }

    protected function _getSchema() {
        return new CM_Model_Schema_Definition(array(
            'name'       => ['type' => 'string'],
            'executedAt' => ['type' => 'DateTime', 'optional' => true],
        ));
    }

    /**
     * @param string $name
     * @return CM_Migration_Model
     */
    static public function create($name) {
        $model = new self();
        $model->setName($name);
        $model->commit();
        return $model;
    }

    /**
     * @param string $name
     * @return CM_Migration_Model
     */
    static public function findByName($name) {
        $cache = CM_Cache_Local::getInstance();
        if (false === ($migrationId = $cache->get($name))) {
            $migrationId = CM_Db_Db::select(self::getTableName(), 'id', array('name' => $name))->fetchColumn();
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

    public static function getTableName() {
        return 'cm_migration';
    }
}
