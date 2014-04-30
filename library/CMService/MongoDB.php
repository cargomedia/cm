<?php

class CMService_MongoDB extends CM_Class_Abstract {

    /** @var CMService_MongoDB|null $_client */
    private $_client = null;

    /**
     * @return MongoClient
     */
    protected function getClient() {
        if (empty($this->_client)) {
            $config = self::_getConfig();
            $this->_client = new MongoClient($config->server, $config->options);
        }

        return $this->_client;
    }

    /**
     * @param string|null $dbName
     * @return MongoDB
     * @throws CM_Exception_Nonexistent
     */
    protected function getDatabase($dbName = null) {
        $client = $this->getClient();

        if ($dbName === null) {
            $config = self::_getConfig();
            $dbName = $config->db;
        }

        // add optional prefix to dbName
        $dbName = CM_Bootloader::getInstance()->getDataPrefix() . $dbName;

        return $client->{$dbName};
    }

    /**
     * @return string
     */
    public static function getNewId() {
        return (string) new MongoId();
    }

    /**
     * @param $collection
     * @return MongoCollection
     */
    public function getCollection($collection) {
        return $this->getDatabase()->{$collection};
    }

    /**
     * @return array
     */
    public function listCollections() {
        return $this->getDatabase()->listCollections();
    }

    /**
     * @param $collection
     * @param $object
     * @return array|bool
     */
    public function insert($collection, &$object) {
        CM_Debug::getInstance()->incStats('mongo', "insert to {$collection}");

        return $this->getCollection($collection)->insert($object);
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
}
