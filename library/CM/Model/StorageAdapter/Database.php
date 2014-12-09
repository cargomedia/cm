<?php

class CM_Model_StorageAdapter_Database extends CM_Model_StorageAdapter_AbstractAdapter {

    public function load($type, array $id) {
        return CM_Db_Db::select($this->_getTableName($type), '*', $id)->fetch();
    }

    public function loadMultiple(array $idTypeList) {
        $types = array();
        $dbEntryToArrayKey = array();

        foreach ($idTypeList as $key => $idType) {
            $type = (int) $idType['type'];
            $id = $idType['id'];
            $types[$type][] = $id;
            foreach ($id as &$idPart) {
                $idPart = (string) $idPart;
            }
            $dbEntryToArrayKey['type:' . $type . 'id:' . serialize($id)] = $key;
        }
        $resultSet = array();
        foreach ($types as $type => $ids) {
            $idColumnList = array_keys($ids[0]);
            $result = CM_Db_Db::selectMultiple($this->_getTableName($type), '*', $ids)->fetchAll();
            foreach ($result as $row) {
                $id = array();
                foreach ($idColumnList as $idColumn) {
                    $id[$idColumn] = $row[$idColumn];
                }
                $key = $dbEntryToArrayKey['type:' . $type . 'id:' . serialize($id)];
                $resultSet[$key] = $row;
            }
        }
        return $resultSet;
    }

    public function save($type, array $id, array $data) {
        CM_Db_Db::update($this->_getTableName($type), $data, $id);
    }

    public function create($type, array $data) {
        $id = CM_Db_Db::insert($this->_getTableName($type), $data);
        if (null === $id) {
            throw new CM_Exception_Invalid('Insert statement did not return an ID');
        }
        return array('id' => (int) $id);
    }

    public function delete($type, array $id) {
        CM_Db_Db::delete($this->_getTableName($type), $id);
    }

    /**
     * @param int $type
     * @return string
     */
    protected function _getTableName($type) {
        $className = CM_Model_Abstract::getClassName($type);
        $tableName = strtolower($className);
        return $tableName;
    }
}
