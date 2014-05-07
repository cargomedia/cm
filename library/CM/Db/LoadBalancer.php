<?php

class CM_Db_LoadBalancer {

    protected $_clientConfigList;
    protected $_client;

    public function __construct(array $clientConfigList) {
        $this->_clientConfigList = $clientConfigList;
    }

    public function getClient() {
        if (!isset($this->_client) && !empty($this->_clientConfigList)) {
            $clientConfig = $this->_clientConfigList[array_rand($this->_clientConfigList)];
            $host = $clientConfig['host'];
            $port = $clientConfig['port'];
            $username = $clientConfig['username'];
            $password = $clientConfig['password'];
            $db = isset($clientConfig['db']) ? $clientConfig['db'] : null;
            $reconnectTimeout = isset($clientConfig['reconnectTimeout']) ? $clientConfig['reconnectTimeout'] : null;
            $this->_client = new CM_Db_Client($host, $port, $username, $password, $db, $reconnectTimeout);
        }
        return $this->_client;
    }
}
