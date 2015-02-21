<?php

class CM_Elasticsearch_Client extends CM_Class_Abstract {

    /** @var Elastica\Client */
    private $_client;

    /** @var bool */
    private $_enabled;

    /**
     * @param array[] $servers
     * @param bool    $enabled
     */
    public function __construct(array $servers, $enabled) {
        $this->_enabled = (bool) $enabled;
        $this->_client = new Elastica\Client(array('servers' => $servers, 'timeout' => 10));
    }

    /**
     * @return bool
     */
    public function getEnabled() {
        return $this->_enabled;
    }

    /**
     * @return \Elastica\Client
     */
    public function getElasticaClient() {
        return $this->_client;
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
