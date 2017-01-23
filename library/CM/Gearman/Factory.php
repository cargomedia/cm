<?php

class CM_Gearman_Factory {

    /**
     * @param array $servers
     * @param int   $workerJobLimit
     * @return CM_Gearman_JobQueue
     */
    protected function createJobQueue(array $servers, $workerJobLimit) {
        $serializer = new CM_Serializer_ArrayConvertible();
        $jobSerializer = new CM_Jobdistribution_Serializer($serializer);
        $publisher = $this->createPublisher($servers, $jobSerializer);
        $jobWorker = $this->createJobWorker($servers, $jobSerializer, $workerJobLimit);
        return new CM_Gearman_JobQueue($publisher, $jobWorker);
    }

    /**
     * @param array                         $servers
     * @param CM_Jobdistribution_Serializer $serializer
     * @return CM_Gearman_Publisher
     */
    protected function createPublisher(array $servers, CM_Jobdistribution_Serializer $serializer) {
        $client = $this->createGearmanClient($servers);
        return new CM_Gearman_Publisher($client, $serializer);
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
     * @param array                         $servers
     * @param CM_Jobdistribution_Serializer $serializer
     * @param int                           $jobLimit
     * @return CM_Gearman_JobWorker
     */
    public function createJobWorker(array $servers, CM_Jobdistribution_Serializer $serializer, $jobLimit) {
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
     * @return CM_Jobdistribution_Serializer
     */
    public function createSerializer() {
        $serializer = new CM_Serializer_ArrayConvertible();
        return new CM_Jobdistribution_Serializer($serializer);
    }

}
