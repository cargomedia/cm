<?php

class CM_Gearman_JobService implements CM_Jobdistribution_QueueInterface {

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
            $this->_worker->registerJob($jobClassName);
        }
        $this->_worker->run();
    }

    public function queue(CM_Jobdistribution_Job_Abstract $job) {
        $this->_client->queue($job);
    }

    public function runSync(CM_Jobdistribution_Job_Abstract $job) {
        return $this->_client->run($job);
    }

    public function runSyncMultiple(array $jobs) {
        return $this->_client->runMultiple($jobs);
    }

}
