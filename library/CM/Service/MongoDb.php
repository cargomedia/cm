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
     * @param string $collection
     * @param array  $object
     * @return array|bool
     */
    public function insert($collection, array $object) {
        CM_Debug::getInstance()->incStats('mongo', "insert to {$collection}");
        $ref = & $object;
        return $this->_getCollection($collection)->insert($ref);
    }

    /**
     * @param string     $collection
     * @param array|null $criteria
     * @param array|null $projection
     * @return array
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
     */
    public function remove($collection, array $criteria, array $options = null) {
        $options = (array) $options;
        CM_Debug::getInstance()->incStats('mongo', "remove from {$collection}");
        return $this->_getCollection($collection)->remove($criteria, $options);
    }

    /**
     * @return string
     */
    public function getNewId() {
        return (string) new MongoId();
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
     * @return MongoDB
     * @throws CM_Exception_Nonexistent
     */
    protected function _getDatabase() {
        $dbName = CM_Bootloader::getInstance()->getDataPrefix() . $this->_config['db'];
        return $this->_getClient()->selectDB($dbName);
    }

    /**
     * @param string $collection
     * @return MongoCollection
     */
    protected function _getCollection($collection) {
        return $this->_getDatabase()->selectCollection($collection);
    }
}
