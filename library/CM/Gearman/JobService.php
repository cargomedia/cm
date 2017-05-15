<?php

class CM_Gearman_JobService implements CM_Jobdistribution_QueueInterface, CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var CM_Gearman_Client */
    private $_client;

    /** @var CM_Gearman_Worker */
    private $_worker;

    /**
     * @param CM_Gearman_Client $publisher
     * @param CM_Gearman_Worker $worker
     */
    public function __construct(CM_Gearman_Client $publisher, CM_Gearman_Worker $worker) {
        $this->_client = $publisher;
        $this->_worker = $worker;
    }

    public function consume() {
        foreach (CM_Jobdistribution_Job_Abstract::getClassChildren() as $jobClassName) {
            $this->_getWorker()->registerJob($jobClassName);
        }
        $this->_worker->run();
    }

    public function queue(CM_Jobdistribution_Job_Abstract $job) {
        $this->_getClient()->queue($job);
    }

    public function runSync(CM_Jobdistribution_Job_Abstract $job) {
        return $this->_getClient()->run($job);
    }

    public function runSyncMultiple(array $jobs) {
        return $this->_getClient()->runMultiple($jobs);
    }

    /**
     * @return CM_Gearman_Worker
     */
    protected function _getWorker() {
        $this->_worker->setServiceManager($this->getServiceManager());
        return $this->_worker;
    }

    /**
     * @return CM_Gearman_Client
     */
    protected function _getClient() {
        return $this->_client;
    }

}
