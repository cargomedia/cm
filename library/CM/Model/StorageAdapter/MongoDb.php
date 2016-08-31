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
        if (!$this->_isValidId($id)) {
            return false;
        }
        $mongoId = new MongoId($id);
        $data = $this->_getMongoDb()->findOne($collectionName, ['_id' => $mongoId]);
        if (null === $data) {
            return false;
        }
        return $data;
    }

    public function loadMultiple(array $idTypeList) {
        $idListByCollection = [];
        $keyListById = [];

        foreach ($idTypeList as $key => $idType) {
            $type = (int) $idType['type'];
            $id = (string) $idType['id']['id'];
            $collectionName = $this->_getCollectionName($type);
            $idListByCollection[$collectionName][] = new MongoId($id);
            $keyListById[$id][] = $key;
        }
        $resultSet = [];
        foreach ($idListByCollection as $collectionName => $idList) {
            $result = $this->_getMongoDb()->find($collectionName, ['_id' => ['$in' => $idList]]);
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
        $mongoId = new MongoId($id);
        $this->_getMongoDb()->update($collectionName, ['_id' => $mongoId], $data);
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
        $mongoId = new MongoId($id);
        $this->_getMongoDb()->remove($collectionName, ['_id' => $mongoId]);
    }

    /**
     * @param string $id
     * @return bool
     */
    protected function _isValidId($id) {
        return (bool) preg_match('/^[0-9a-fA-F]{24}$/', (string) $id);
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
