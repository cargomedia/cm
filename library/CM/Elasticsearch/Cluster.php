<?php

class CM_Elasticsearch_Cluster extends CM_Class_Abstract {

    /** @var Elastica\Client[] */
    private $_clients;

    /** @var bool */
    private $_enabled;

    /**
     * @param array[] $servers
     * @param bool    $enabled
     */
    public function __construct(array $servers, $enabled) {
        $this->setEnabled($enabled);
        foreach ($servers as $server) {
            $config = array_merge($server, ['timeout' => 10]);
            $this->_clients[] = new Elastica\Client($config);
        }
    }

    /**
     * @return \Elastica\Client[]
     */
    public function getClients() {
        return $this->_clients;
    }

    /**
     * @return \Elastica\Client
     */
    public function getRandomClient() {
        $index = array_rand($this->_clients);
        return $this->_clients[$index];
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
     * @param CM_Elasticsearch_Type_Abstract[] $types
     * @param array|null                       $data
     * @return array
     */
    public function query(array $types, array $data = null) {
        if (!$this->getEnabled()) {
            return array();
        }
        CM_Debug::getInstance()->incStats('search', json_encode($data));

        $search = new Elastica\Search($this->_client);
        foreach ($types as $type) {
            $search->addIndex($type->getIndex());
            $search->addType($type->getType());
        }
        try {
            $response = $this->_client->request($search->getPath(), 'GET', $data);
        } catch (Elastica\Exception\ConnectionException $ex) {
            foreach ($this->_client->getConnections() as $connection) {
                $connection->setEnabled();
            }
            throw $ex;
        }
        return $response->getData();
    }
}
