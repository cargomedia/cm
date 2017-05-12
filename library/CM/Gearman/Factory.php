<?php

class CM_Gearman_Factory {

    /**
     * @param array $servers
     * @param int   $workerJobLimit
     * @return CM_Gearman_JobService
     */
    protected function createJobService(array $servers, $workerJobLimit) {
        $serializer = new CM_Serializer_ArrayConvertible();
        $jobSerializer = new CM_Jobdistribution_JobSerializer($serializer);
        $client = $this->createClient($servers, $jobSerializer);
        $worker = $this->createWorker($servers, $jobSerializer, $workerJobLimit);
        return new CM_Gearman_JobService($client, $worker);
    }

    /**
     * @param array                            $servers
     * @param CM_Jobdistribution_JobSerializer $serializer
     * @return CM_Gearman_Client
     */
    public function createClient(array $servers, CM_Jobdistribution_JobSerializer $serializer) {
        $client = $this->_createGearmanClient($servers);
        return new CM_Gearman_Client($client, $serializer);
    }

    /**
     * @param array                            $servers
     * @param CM_Jobdistribution_JobSerializer $serializer
     * @param int                              $jobLimit
     * @return CM_Gearman_Worker
     */
    public function createWorker(array $servers, CM_Jobdistribution_JobSerializer $serializer, $jobLimit) {
        $worker = $this->_createGearmanWorker($servers);
        return new CM_Gearman_Worker($worker, $serializer, $jobLimit);
    }

    /**
     * @param array $servers
     * @return GearmanWorker
     * @throws CM_Exception
     */
    public function _createGearmanWorker(array $servers) {
        if (!extension_loaded('gearman')) {
            throw new CM_Exception('Missing `gearman` extension');
        }
        $worker = new GearmanWorker();
        foreach ($servers as $server) {
            $worker->addServer($server['host'], $server['port']);
        }
        return $worker;
    }

    /**
     * @param array $servers
     * @return GearmanClient
     * @throws CM_Exception
     */
    protected function _createGearmanClient(array $servers) {
        if (!extension_loaded('gearman')) {
            throw new CM_Exception('Missing `gearman` extension');
        }
        $client = new GearmanClient();
        foreach ($servers as $server) {
            $client->addServer($server['host'], $server['port']);
        }
        return $client;
    }

    /**
     * @return CM_Jobdistribution_JobSerializer
     */
    public function createSerializer() {
        $serializer = new CM_Serializer_ArrayConvertible();
        return new CM_Jobdistribution_JobSerializer($serializer);
    }

}
