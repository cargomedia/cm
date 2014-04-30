<?php

class CM_Service_MongoDb extends CM_Class_Abstract {

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
     * @param $collection
     * @return MongoCollection
     */
    public function getCollection($collection) {
        return $this->_getDatabase()->{$collection};
    }

    /**
     * @return array
     */
    public function listCollections() {
        return $this->_getDatabase()->listCollections();
    }

    /**
     * @param $collection
     * @param $object
     * @return array|bool
     */
    public function insert($collection, $object) {
        CM_Debug::getInstance()->incStats('mongo', "insert to {$collection}");
        $ref = & $object;
        return $this->getCollection($collection)->insert($ref);
    }

    /**
     * @param string     $collection
     * @param array      $query
     * @param array|null $fields
     * @return array
     */
    public function findOne($collection, $query, $fields = null) {
        $fields = ($fields !== null) ? $fields : array();
        CM_Debug::getInstance()->incStats('mongo',
            "findOne in {$collection}: " . serialize(array('fields' => $fields) + $query));

        return $this->getCollection($collection)->findOne($query, $fields);
    }

    /**
     * @param string     $collection
     * @param array      $query
     * @param array|null $fields
     * @return MongoCursor
     */
    public function find($collection, $query, $fields = null) {
        $fields = ($fields !== null) ? $fields : array();
        CM_Debug::getInstance()->incStats('mongo', "find in {$collection}: " . serialize(array('fields' => $fields) + $query));

        return $this->getCollection($collection)->find($query);
    }

    /**
     * @param string $collection
     * @param array  $query
     * @param int    $limit
     * @param int    $skip
     * @return int
     */
    public function count($collection, $query, $limit = 0, $skip = 0) {
        CM_Debug::getInstance()->incStats('mongo', "count in {$collection}: " . serialize($query));

        return $this->getCollection($collection)->count($query, $limit, $skip);
    }

    /**
     * @param $collection
     * @return array
     */
    public function drop($collection) {
        CM_Debug::getInstance()->incStats('mongo', "drop {$collection}: ");

        return $this->getCollection($collection)->drop();
    }

    /**
     * @param string     $collection
     * @param array      $criteria
     * @param array      $newObject
     * @param array|null $options
     * @return MongoCursor
     */
    public function update($collection, $criteria, $newObject, $options = null) {
        CM_Debug::getInstance()->incStats('mongo', "Update {$collection}");
        $options = ($options !== null) ? $options : array();

        return $this->getCollection($collection)->update($criteria, $newObject, $options);
    }

    /**
     * @param string     $collection
     * @param array      $criteria
     * @param array|null $options
     * @return mixed
     */
    public function remove($collection, $criteria, $options = null) {
        CM_Debug::getInstance()->incStats('mongo', "remove from {$collection}");
        $options = ($options !== null) ? $options : array();

        return $this->getCollection($collection)->remove($criteria, $options);
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
     * @param string|null $dbName
     * @return MongoDB
     * @throws CM_Exception_Nonexistent
     */
    protected function _getDatabase($dbName = null) {
        $client = $this->_getClient();

        if (null === $dbName) {
            $dbName = $this->_config['db'];
        }
        $dbName = CM_Bootloader::getInstance()->getDataPrefix() . $dbName;

        return $client->{$dbName};
    }
}
