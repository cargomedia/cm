<?php

class CM_Gearman_JobService implements CM_Jobdistribution_QueueInterface, CM_Jobdistribution_RPCInterface {
    
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

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     */
    public function publish(CM_Jobdistribution_Job_Abstract $job) {
        $this->_client->queue($job);
    }

    public function consume() {
        foreach (CM_Jobdistribution_Job_Abstract::getClassChildren() as $jobClassName) {
            $this->_worker->registerJob($jobClassName);
        }
        $this->_worker->run();
    }

    public function run(CM_Jobdistribution_Job_Abstract $job) {
        return $this->_client->run($job);
    }

    public function runMultiple(array $jobs) {
        return $this->_client->runMultiple($jobs);
    }

} 
