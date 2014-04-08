<?php

class CM_Service_MongoDB {

    private $_client = null;

    protected $_config;

    public function __construct(array $config) {
        $this->_config = $config;
    }

    /**
     * @return MongoClient
     */
    public function getClient() {
        if (empty($this->_client)) {
            $this->_client = new MongoClient($this->_config['server'], $this->_config['options']);
        }
        return $this->_client;
    }

    /**
     * @param string|null $dbName
     * @return MongoDB
     * @throws CM_Exception_Nonexistent
     */
    public function getDatabase($dbName = null) {
        $client = $this->getClient();

        if ($dbName === null) {
            if (empty($this->_config['dbName'])) {
                throw new CM_Exception_Nonexistent('MongoDB service dbName not set.');
            }
            $dbName = $this->_config['dbName'];
        }

        return $client->{$dbName};
    }

    /**
     * @return string
     */
    public static function getNewId() {
        return (string)new MongoId();
    }
}
