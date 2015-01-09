<?php

class CM_Model_StorageAdapter_MongoDb extends CM_Model_StorageAdapter_AbstractAdapter {

    public function load($type, array $id) {
        $collectionName = $this->_getCollectionName($type);
        $data = $this->_getMongoDb()->findOne($collectionName, $id);
        if (null === $data) {
            return false;
        }
        return $data;
    }

    public function loadMultiple(array $idTypeList) {
        $types = [];
        $dbEntryToArrayKey = [];

        foreach ($idTypeList as $key => $idType) {
            $type = (int) $idType['type'];
            $id = $idType['id']['_id'];
            $types[$type][] = $id;
            $dbEntryToArrayKey['type:' . $type . 'id:' . serialize($id)] = $key;
        }
        $resultSet = [];
        foreach ($types as $type => $ids) {
            $result = $this->_getMongoDb()->find($this->_getCollectionName($type), ['_id' => ['$in' => $ids]]);
            foreach ($result as $row) {
                $id = $row['_id'];
                $key = $dbEntryToArrayKey['type:' . $type . 'id:' . serialize($id)];
                $resultSet[$key] = $row;
            }
        }
        return $resultSet;
    }

    public function save($type, array $id, array $data) {
        $type = (int) $type;
        $data = ['_type' => $type] + $data;
        $collectionName = $this->_getCollectionName($type);
        $this->_getMongoDb()->update($collectionName, $id, $data);
    }

    public function create($type, array $data) {
        $type = (int) $type;
        $data = ['_type' => $type] + $data;
        $collectionName = $this->_getCollectionName($type);
        $mongoId = $this->_getMongoDb()->insert($collectionName, $data);
        return ['_id' => $mongoId, '_type' => $type];
    }

    public function delete($type, array $id) {
        $collectionName = $this->_getCollectionName($type);
        $this->_getMongoDb()->remove($collectionName, $id);
    }

    /**
     * @param int $type
     * @return string
     */
    protected function _getCollectionName($type) {
        $className = CM_Model_Abstract::getClassName($type);
        /** @var CM_Model_Abstract $className */
        return $className::getTableName();
    }

    /**
     * @return CM_MongoDb_Client
     */
    protected function _getMongoDb() {
        return CM_Service_Manager::getInstance()->getMongoDb();
    }
}
