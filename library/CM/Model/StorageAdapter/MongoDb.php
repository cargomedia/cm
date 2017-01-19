<?php

class CM_Model_StorageAdapter_MongoDb extends CM_Model_StorageAdapter_AbstractAdapter implements CM_Model_StorageAdapter_FindableInterface {

    public function findByData($type, array $data) {
        $result = $this->_getMongoDb()->findOne($this->_getCollectionName($type), $data, ['_id']);
        if (null === $result) {
            return null;
        }
        $mongoId = $result['_id'];
        return ['id' => (string) $mongoId];
    }

    public function load($type, array $id) {
        $type = (int) $type;
        $id = (string) $id['id'];
        $collectionName = $this->_getCollectionName($type);
        if (!CM_MongoDb_Client::isValidObjectId($id)) {
            return false;
        }
        $mongoDb = $this->_getMongoDb();
        $data = $mongoDb->findOne($collectionName, ['_id' => CM_MongoDb_Client::getObjectId($id)]);
        if (null === $data) {
            return false;
        }
        return $data;
    }

    public function loadMultiple(array $idTypeList) {
        $idListByCollection = [];
        $keyListById = [];
        $mongoDb = $this->_getMongoDb();

        foreach ($idTypeList as $key => $idType) {
            $type = (int) $idType['type'];
            $id = (string) $idType['id']['id'];
            $collectionName = $this->_getCollectionName($type);
            $idListByCollection[$collectionName][] = CM_MongoDb_Client::getObjectId($id);
            $keyListById[$id][] = $key;
        }
        $resultSet = [];
        foreach ($idListByCollection as $collectionName => $idList) {
            $result = $mongoDb->find($collectionName, ['_id' => ['$in' => $idList]]);
            foreach ($result as $row) {
                $id = (string) $row['_id'];
                foreach ($keyListById[$id] as $key) {
                    $resultSet[$key] = $row;
                }
            }
        }
        return $resultSet;
    }

    public function save($type, array $id, array $data) {
        $type = (int) $type;
        $id = (string) $id['id'];
        $data = ['_type' => $type] + $data;
        $collectionName = $this->_getCollectionName($type);
        $mongoDb = $this->_getMongoDb();
        $mongoDb->replaceOne($collectionName, ['_id' => CM_MongoDb_Client::getObjectId($id)], $data);
    }

    public function create($type, array $data) {
        $type = (int) $type;
        $data = ['_type' => $type] + $data;
        $collectionName = $this->_getCollectionName($type);
        $mongoId = $this->_getMongoDb()->insert($collectionName, $data);
        return ['id' => (string) $mongoId];
    }

    public function delete($type, array $id) {
        $type = (int) $type;
        $id = (string) $id['id'];
        $collectionName = $this->_getCollectionName($type);
        $mongoDb = $this->_getMongoDb();
        $mongoDb->deleteOne($collectionName, ['_id' => CM_MongoDb_Client::getObjectId($id)]);
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
