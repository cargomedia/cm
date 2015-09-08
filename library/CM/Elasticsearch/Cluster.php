<?php

class CM_Elasticsearch_Cluster extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var array */
    private $_serverList;

    /** @var Elastica\Client[] */
    private $_clients;

    /** @var Elastica\Client[] */
    private $_longTimeoutClients;

    /** @var bool */
    private $_enabled;

    /**
     * @param array[]   $serverList
     * @param bool|null $disabled
     */
    public function __construct(array $serverList, $disabled = null) {
        $this->setEnabled(!$disabled);
        $this->_serverList = $serverList;
        $this->_clients = $this->_initClients(10);
    }

    /**
     * @return \Elastica\Client[]
     */
    public function getClientList() {
        return $this->_clients;
    }

    /**
     * @param boolean|null $selectLongTimeoutClient
     * @return \Elastica\Client
     */
    public function getRandomClient($selectLongTimeoutClient = null) {
        if (true === $selectLongTimeoutClient && null !== $this->_longTimeoutClients) {
            $index = array_rand($this->_longTimeoutClients);
            return $this->_longTimeoutClients[$index];
        }
        else {
            $index = array_rand($this->_clients);
            return $this->_clients[$index];
        }
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled) {
        $this->_enabled = (bool) $enabled;
    }

    /**
     * @return bool
     */
    public function getEnabled() {
        return $this->_enabled;
    }

    /**
     * @param boolean|null $useLongTimeout
     * @return CM_Elasticsearch_Type_Abstract[]
     */
    public function getTypes($useLongTimeout = null) {
        $types = CM_Util::getClassChildren('CM_Elasticsearch_Type_Abstract');

        if (true === $useLongTimeout && null === $this->_longTimeoutClients) {
            $this->_longTimeoutClients = $this->_initClients(20);
        }

        return \Functional\map($types, function ($className) use ($useLongTimeout) {
            return new $className($this->getRandomClient($useLongTimeout));
        });
    }

    /**
     * @param string $indexName
     * @return CM_Elasticsearch_Type_Abstract
     * @throws CM_Exception_Invalid
     */
    public function findType($indexName) {
        return \Functional\first($this->getTypes(), function (CM_Elasticsearch_Type_Abstract $type) use ($indexName) {
            return $type->getIndex()->getName() === $indexName;
        });
    }

    /**
     * @param CM_Elasticsearch_Type_Abstract[] $types
     * @param array|null                       $data
     * @return array
     */
    public function query(array $types, array $data = null) {
        if (!$this->getEnabled()) {
            return array();
        }
        $this->getServiceManager()->getDebug()->incStats('search', json_encode($data));
        $client = $this->getRandomClient();

        $search = new Elastica\Search($client);
        foreach ($types as $type) {
            $search->addIndex($type->getIndex());
            $search->addType($type->getType());
        }
        try {
            $response = $client->request($search->getPath(), 'GET', $data);
        } catch (Elastica\Exception\ConnectionException $ex) {
            foreach ($client->getConnections() as $connection) {
                $connection->setEnabled();
            }
            throw $ex;
        }
        return $response->getData();
    }

    /**
     * @param int $timeout
     * @return array
     */
    private function _initClients($timeout) {
        $timeout = (int) $timeout;
        if (0 === $timeout) {
            $timeout = 10;
        }

        $clients = [];
        foreach ($this->_serverList as $server) {
            $config = array_merge(['timeout' => $timeout], $server);
            $clients[] = new Elastica\Client($config);
        }
        return $clients;
    }
}
