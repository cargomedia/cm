<?php

class CM_Elasticsearch_Cluster extends CM_Class_Abstract {

    /** @var bool */
    private $_enabled;

    /** @var CM_Elasticsearch_Client */
    protected $_client;

    /**
     * @param CM_Elasticsearch_Client $client
     * @param bool|null               $disabled
     */
    public function __construct(CM_Elasticsearch_Client $client, $disabled = null) {
        $this->_client = $client;
        $this->setEnabled(!$disabled);
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
