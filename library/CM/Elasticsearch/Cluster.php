<?php

class CM_Elasticsearch_Cluster extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var Elastica\Client[] */
    private $_clients;

    /** @var bool */
    private $_enabled;

    /**
     * @param array[]   $servers
     * @param bool|null $disabled
     */
    public function __construct(array $servers, $disabled = null) {
        $this->setEnabled(!$disabled);
        foreach ($servers as $server) {
            $config = array_merge(['timeout' => 30], $server);
            $this->_clients[] = new Elastica\Client($config);
        }
    }

    /**
     * @return \Elastica\Client[]
     */
    public function getClientList() {
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
     * @return CM_Elasticsearch_Type_Abstract[]
     */
    public function getTypes() {
        $types = CM_Util::getClassChildren('CM_Elasticsearch_Type_Abstract');
        return \Functional\map($types, function ($className) {
            return new $className($this->getRandomClient());
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
}
