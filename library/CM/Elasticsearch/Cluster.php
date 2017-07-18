<?php

class CM_Elasticsearch_Cluster extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var bool */
    private $_enabled;

    /** @var CM_Elasticsearch_Client */
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

        $elasticsearchClient = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        //By default it uses RoundRobinSelector and number of retries equals to nodes quantity

        $this->_client = new CM_Elasticsearch_Client($elasticsearchClient, $this->getServiceManager()->getDebug());
    }

    /**
     * @return CM_Elasticsearch_Client
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
    public function getTypeList() {
        $types = CM_Util::getClassChildren('CM_Elasticsearch_Type_Abstract');
        return \Functional\map($types, function ($className) {
            return new $className($this->getClient());
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
        $indexNameList = [];
        $typeNameList = [];
        foreach ($types as $type) {
            $indexNameList[] = $type->getIndexName();
            $typeNameList[] = $type->getTypeName();
        }
        return $this->getClient()->search($indexNameList, $typeNameList, $data);
    }
}
