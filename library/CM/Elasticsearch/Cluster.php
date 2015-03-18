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
            $config = array_merge($server, ['timeout' => 10]);
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
    public function getTypeByIndexName($indexName) {
        $indexName = (string) $indexName;
        $type = \Functional\first($this->getTypes(), function (CM_Elasticsearch_Type_Abstract $type) use ($indexName) {
            return $type->getIndex()->getName() === $indexName;
        });
        if (!$type) {
            throw new CM_Exception_Invalid('No type with such index name: ' . $indexName);
        }
        return $type;
    }

    /**
     * @param CM_Elasticsearch_Type_Abstract $type
     */
    public function createIndex(CM_Elasticsearch_Type_Abstract $type) {
        $type->createVersioned();
        $type->getIndex()->refresh();
    }

    /**
     * @param CM_Elasticsearch_Type_Abstract $type
     * @throws CM_Exception_Invalid
     */
    public function updateIndex(CM_Elasticsearch_Type_Abstract $type) {
        $redis = $this->getServiceManager()->getRedis();
        $indexName = $type->getIndex()->getName();
        $key = 'Search.Updates_' . $type->getType()->getName();
        try {
            $ids = $redis->sFlush($key);
            $ids = array_filter(array_unique($ids));
            $type->update($ids);
            $type->getIndex()->refresh();
        } catch (Exception $e) {
            $message = $indexName . '-updates failed.' . PHP_EOL;
            if (isset($ids)) {
                $message .= 'Re-adding ' . count($ids) . ' ids to queue.' . PHP_EOL;
                foreach ($ids as $id) {
                    $redis->sAdd($key, $id);
                }
            }
            $message .= 'Reason: ' . $e->getMessage() . PHP_EOL;
            throw new CM_Exception_Invalid($message);
        }
    }

    /**
     * @param CM_Elasticsearch_Type_Abstract $type
     */
    public function deleteIndex(CM_Elasticsearch_Type_Abstract $type) {
        if ($type->getIndex()->exists()) {
            $type->getIndex()->delete();
        }
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
