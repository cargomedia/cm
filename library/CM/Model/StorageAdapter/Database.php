<?php

class CM_Model_StorageAdapter_Database extends CM_Model_StorageAdapter_AbstractAdapter implements CM_Model_StorageAdapter_FindableInterface, CM_Model_StorageAdapter_ReplaceableInterface {

    public function load($type, array $id) {
        return CM_Db_Db::select($this->_getTableName($type), '*', $id)->fetch();
    }

    public function save($type, array $id, array $data) {
        CM_Db_Db::update($this->_getTableName($type), $data, $id);
    }

    public function create($type, array $data) {
        $id = CM_Db_Db::insert($this->_getTableName($type), $data, null, null, 'INSERT');
        if (null === $id) {
            throw new CM_Exception_Invalid('Insert statement did not return an ID');
        }
        return array('id' => (int) $id);
    }

    public function replace($type, array $data) {
        $id = CM_Db_Db::insert($this->_getTableName($type), $data, null, null, 'REPLACE');
        if (null === $id) {
            throw new CM_Exception_Invalid('Replace statement did not return an ID');
        }
        return array('id' => (int) $id);
    }

    public function delete($type, array $id) {
        CM_Db_Db::delete($this->_getTableName($type), $id);
    }

    public function findByData($type, array $data) {
        $result = CM_Db_Db::select($this->_getTableName($type), array('id'), $data)->fetch();
        if (false === $result) {
            $result = null;
        }
        return $result;
    }

    /**
     * @param int $type
     * @return string
     */
    protected function _getTableName($type) {
        $className = CM_Model_Abstract::getClassName($type);
        /** @var CM_Model_Abstract $className */
        return $className::getTableName();
    }
}
