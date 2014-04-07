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
            echo "Creating instance of MongoClient connected to: {$this->_config['host']}" . PHP_EOL;
            $this->_client = new MongoClient();
        } else {
            echo "Using cached instance of MongoClient connected to: {$this->_config['host']}" . PHP_EOL;
        }
        return $this->_client;
    }
}
