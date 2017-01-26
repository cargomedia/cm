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
        $jobWorker = $this->createJobWorker($servers, $jobSerializer, $workerJobLimit);
        return new CM_Gearman_JobService($client, $jobWorker);
    }

    /**
     * @param array                            $servers
     * @param CM_Jobdistribution_JobSerializer $serializer
     * @return CM_Gearman_Client
     */
    protected function createClient(array $servers, CM_Jobdistribution_JobSerializer $serializer) {
        $client = $this->createGearmanClient($servers);
        return new CM_Gearman_Client($client, $serializer);
    }

    /**
     * @param array $servers
     * @return GearmanClient
     * @throws CM_Exception
     */
    public function createGearmanClient(array $servers) {
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
     * @param array                            $servers
     * @param CM_Jobdistribution_JobSerializer $serializer
     * @param int                              $jobLimit
     * @return CM_Gearman_JobWorker
     */
    public function createJobWorker(array $servers, CM_Jobdistribution_JobSerializer $serializer, $jobLimit) {
        $worker = $this->createGearmanWorker($servers);
        return new CM_Gearman_JobWorker($worker, $serializer, $jobLimit);
    }

    /**
     * @param array $servers
     * @return GearmanWorker
     * @throws CM_Exception
     */
    public function createGearmanWorker(array $servers) {
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
     * @return CM_Jobdistribution_JobSerializer
     */
    public function createSerializer() {
        $serializer = new CM_Serializer_ArrayConvertible();
        return new CM_Jobdistribution_JobSerializer($serializer);
    }

}
