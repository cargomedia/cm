<?php

class CM_Service_MongoDb extends CM_Service_ManagerAware {

    /** @var CM_Service_MongoDB|null $_client */
    private $_client = null;

    /** @var array */
    private $_config;

    /**
     * @param array $config
     */
    public function __construct(array $config) {
        $this->_config = $config;
    }

    /**
     * @return string[]
     */
    public function listCollectionNames() {
        return array_map(function (MongoCollection $collection) {
            return $collection->getName();
        }, $this->_getDatabase()->listCollections());
    }

    /**
     * @return MongoDB
     */
    protected function _getDatabase() {
        $dbName = CM_Bootloader::getInstance()->getDataPrefix() . $this->_config['db'];
        return $this->_getClient()->selectDB($dbName);
    }

    /**
     * @return MongoClient
     */
    protected function _getClient() {
        if (null === $this->_client) {
            $this->_client = new MongoClient($this->_config['server'], $this->_config['options']);
        }

        return $this->_client;
    }

    /**
     * @param string $collection
     * @param array  $object
     * @return array|bool
     *
     * @see http://php.net/manual/en/mongocollection.insert.php
     */
    public function insert($collection, array $object) {
        CM_Debug::getInstance()->incStats('mongo', "insert to {$collection}");
        $ref = & $object;

        return $this->_getCollection($collection)->insert($ref);
    }

    /**
     * @param string $collection
     * @return MongoCollection
     */
    protected function _getCollection($collection) {
        return $this->_getDatabase()->selectCollection($collection);
    }

    /**
     * @param string     $collection
     * @param array|null $criteria
     * @param array|null $projection
     * @return array
     *
     * @see http://php.net/manual/en/mongocollection.findone.php
     */
    public function findOne($collection, array $criteria = null, array $projection = null) {
        $criteria = (array) $criteria;
        $projection = (array) $projection;
        CM_Debug::getInstance()->incStats('mongo',
            "findOne in {$collection}: " . serialize(array('projection' => $projection) + $criteria));

        return $this->_getCollection($collection)->findOne($criteria, $projection);
    }

    /**
     * @param string     $collection
     * @param array|null $criteria
     * @param array|null $projection
     * @return MongoCursor
     *
     * @see http://php.net/manual/en/mongocollection.find.php
     */
    public function find($collection, array $criteria = null, array $projection = null) {
        $criteria = (array) $criteria;
        $projection = (array) $projection;
        CM_Debug::getInstance()->incStats('mongo', "find in {$collection}: " . serialize(array('fields' => $projection) + $criteria));

        return $this->_getCollection($collection)->find($criteria, $projection);
    }

    /**
     * @param string     $collection
     * @param array|null $criteria
     * @param int|null   $limit
     * @param int|null   $offset
     * @return int
     *
     * @see http://php.net/manual/en/mongocollection.count.php
     */
    public function count($collection, array $criteria = null, $limit = null, $offset = null) {
        $criteria = (array) $criteria;
        $limit = (int) $limit;
        $offset = (int) $offset;
        CM_Debug::getInstance()->incStats('mongo', "count in {$collection}: " . serialize($criteria));

        return $this->_getCollection($collection)->count($criteria, $limit, $offset);
    }

    /**
     * @param string $collection
     * @return array
     *
     * @see http://php.net/manual/en/mongocollection.drop.php
     */
    public function drop($collection) {
        CM_Debug::getInstance()->incStats('mongo', "drop {$collection}: ");

        return $this->_getCollection($collection)->drop();
    }

    /**
     * @param string     $collection
     * @param array      $criteria
     * @param array      $newObject
     * @param array|null $options
     * @return MongoCursor
     *
     * @see http://php.net/manual/en/mongocollection.update.php
     */
    public function update($collection, array $criteria, array $newObject, array $options = null) {
        $options = (array) $options;
        CM_Debug::getInstance()->incStats('mongo', "Update {$collection}");

        return $this->_getCollection($collection)->update($criteria, $newObject, $options);
    }

    /**
     * @param string     $collection
     * @param array      $criteria
     * @param array|null $options
     * @return mixed
     *
     * @see http://php.net/manual/en/mongocollection.remove.php
     */
    public function remove($collection, array $criteria, array $options = null) {
        $options = (array) $options;
        CM_Debug::getInstance()->incStats('mongo', "remove from {$collection}");
        return $this->_getCollection($collection)->remove($criteria, $options);
    }

    /**
     * @param string $collection
     * @param array  $keys
     * @param array  $options
     * @return mixed
     *
     * @see http://php.net/manual/en/mongocollection.createindex.php
     */
    public function createIndex($collection, array $keys, array $options = null) {
        $options = (array) $options;
        $result = $this->_getCollection($collection)->createIndex($keys, $options);
        $this->_checkIndexOperationForError($result);
        return $result;
    }

    /**
     * @param array|bool $result
     * @throws CM_Exception
     */
    protected function _checkIndexOperationForError($result) {
        if (true !== $result && empty($result['ok'])) {
            throw new CM_Exception('Index operation failed.');
        }
    }

    /**
     * @param string $collection
     * @param string $indexName
     * @return array
     *
     */
    public function deleteIndex($collection, $indexName) {
        $result = $this->_getDatabase()->command(array("deleteIndexes" => $collection, "index" => $indexName));
        $this->_checkIndexOperationForError($result);
        return $result;
    }

    /**
     * @param string $collection
     * @return array
     *
     * @see http://php.net/manual/en/mongocollection.getindexinfo.php
     */
    public function getIndexInfo($collection) {
        return $this->_getCollection($collection)->getIndexInfo();
    }

    /**
     * @return string
     */
    public function getNewId() {
        return (string) new MongoId();
    }
}
