<?php

class CM_Elasticsearch_Client extends CM_Class_Abstract {

    /** @var Elastica\Client */
    private $_client;

    /** @var CM_Elasticsearch_Client */
    private static $_instance;

    public function __construct() {
        $this->_client = new Elastica\Client(array('servers' => self::_getConfig()->servers, 'timeout' => 10));
    }

    /**
     * @return bool
     */
    public function getEnabled() {
        return (bool) self::_getConfig()->enabled;
    }

    /**
     * @param CM_Elasticsearch_Type_Abstract[] $types
     * @param array|null                  $data
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
        $response = $this->_client->request($search->getPath(), 'GET', $data);
        return $response->getData();
    }

    /**
     * @return CM_Elasticsearch_Client
     */
    public static function getInstance() {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}
