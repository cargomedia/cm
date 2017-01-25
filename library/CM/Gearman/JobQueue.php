<?php

class CM_Gearman_JobQueue implements CM_Jobdistribution_QueueInterface {
    
    /** @var CM_Gearman_Publisher */
    private $_publisher;
    
    /** @var CM_Gearman_JobWorker */
    private $_worker;

    /**
     * @param CM_Gearman_Publisher $publisher
     * @param CM_Gearman_JobWorker $worker
     */
    public function __construct(CM_Gearman_Publisher $publisher, CM_Gearman_JobWorker $worker) {
        $this->_publisher = $publisher;
        $this->_worker = $worker;
    }

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     */
    public function publish(CM_Jobdistribution_Job_Abstract $job) {
        $this->_publisher->queue($job);
    }

    public function consume() {
        foreach (CM_Jobdistribution_Job_Abstract::getClassChildren() as $jobClassName) {
            $this->_worker->registerJob($jobClassName);
        }
        $this->_worker->run();
    }

} 
