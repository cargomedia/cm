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
    public static function create($name) {
        $model = new self();
        $model->setName($name);
        $model->commit();
        return $model;
    }

    /**
     * @param string $name
     * @return static|null
     */
    public static function findByName($name) {
        return static::findByAttributes([
            'name' => $name,
        ]);
    }

    /**
     * @param array $data
     * @return static|null
     */
    public static function findByAttributes(array $data) {
        $cache = CM_Cache_Local::getInstance();
        $cacheKey = CM_CacheConst::Migration_ByAttribute;
        foreach ($data as $fieldName => $fieldValue) {
            $cacheKey .= '_name:' . $fieldName . '_value:' . $fieldValue;
        }
        if (false === ($id = $cache->get($cacheKey))) {
            /** @var CM_Model_StorageAdapter_Database $persistence */
            $persistence = self::_getStorageAdapter('CM_Model_StorageAdapter_Database');
            $type = self::getTypeStatic();
            $result = $persistence->findByData($type, $data);
            $id = $result['id'];
            $cache->set($cacheKey, $id);
        }
        if (!$id) {
            return null;
        }
        return new static($id);
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_Database';
    }

    public static function getTableName() {
        return 'cm_migration';
    }
}
