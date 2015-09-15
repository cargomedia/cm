<?php

class CM_Elasticsearch_Cluster extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var bool */
    private $_enabled;

    /** @var Elasticsearch\Client */
    protected $_client;

    /**
     * @param array[]   $servers
     * @param bool|null $disabled
     */
    public function __construct(array $servers, $disabled = null) {
        $this->setEnabled(!$disabled);

        $hosts = array_map(function (array $el) {
            return $el['host'] . (!empty($el['port']) ? ':' . $el['port'] : '');
        }, $servers);

        $this->_client = \Elasticsearch\ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
        //By default it uses RoundRobinSelector and number of retries equals to nodes quantity
        //TODO probably use Service Manager to obtain ClientBuilder, or even pass ClientBuilder instance from outside.
    }

    /**
     * @return \Elasticsearch\Client
     */
    public function getClient() {
        return $this->_client;
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
            return new $className($this->getClient());
        });
    }

    /**
     * @param string $indexName
     * @return CM_Elasticsearch_Type_Abstract
     * @throws CM_Exception_Invalid
     */
    public function findType($indexName) {
        return \Functional\first($this->getTypes(), function (CM_Elasticsearch_Type_Abstract $type) use ($indexName) {
            return $type->getIndexName() === $indexName;
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
        $client = $this->getClient();

        $indexNameList = [];
        $typeNameList = [];
        foreach ($types as $type) {
            $indexNameList[] = $type->getIndexName();
            $typeNameList[] = $type->getTypeName();
        }

        $params = [
            'index' => join(',', $indexNameList),
            'type'  => join(',', $typeNameList),
            'body'  => $data,
        ];

        $response = $client->search($params);
        //TODO probably handle exceptions

        return $response;
    }
}
