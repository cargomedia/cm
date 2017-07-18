<?php

class CM_Elasticsearch_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param array     $servers
     * @param bool|null $disabled
     * @return CM_Elasticsearch_Cluster
     */
    public function createCluster(array $servers, $disabled = null) {
        $debug = $this->getServiceManager()->getDebug();
        $client = $this->createClient($servers, $debug);
        return new CM_Elasticsearch_Cluster($client, $disabled);
    }

    /**
     * @param array         $servers
     * @param CM_Debug|null $debug
     * @return CM_Elasticsearch_Client
     */
    public function createClient(array $servers, CM_Debug $debug = null) {
        $vendorClient = $this->_createVendorClient($servers);
        return new CM_Elasticsearch_Client($vendorClient, $debug);
    }

    /**
     * @param array $servers
     * @return \Elasticsearch\Client
     */
    protected function _createVendorClient(array $servers) {
        $hosts = array_map(function (array $el) {
            return $el['host'] . (!empty($el['port']) ? ':' . $el['port'] : '');
        }, $servers);

        //By default it uses RoundRobinSelector and number of retries equals to nodes quantity
        return \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
    }
}
