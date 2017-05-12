<?php

final class CM_EventHandler_EventHandler implements CM_EventHandler_EventHandlerInterface {

    use CM_EventHandler_EventHandlerTrait;

    /** @var  CM_Jobdistribution_QueueInterface */
    private $_jobQueue;

    /**
     * @param CM_Jobdistribution_QueueInterface $jobQueue
     */
    public function __construct(CM_Jobdistribution_QueueInterface $jobQueue = null) {
        if (null !== $jobQueue) {
            $jobQueue = CM_Service_Manager::getInstance()->getJobQueue();
        }
        $this->_jobQueue = $jobQueue;
    }

    /**
     * @param string $event
     * @param string $jobClassName
     * @param array  $defaultJobParams
     * @throws CM_Exception_Invalid
     */
    public function bindJob($event, $jobClassName, array $defaultJobParams = null) {
        $reflectionClass = new ReflectionClass($jobClassName);
        if (!$reflectionClass->isSubclassOf(CM_Jobdistribution_Job_Abstract::class) || $reflectionClass->isAbstract()) {
            throw new CM_Exception_Invalid('Invalid job class', null, ['className' => $jobClassName]);
        }
        $event = (string) $event;
        $defaultJobParams = (array) $defaultJobParams;
        $this->bind($event, function (array $jobParams = null) use ($reflectionClass, $defaultJobParams) {
            $jobParams = (array) $jobParams;
            $jobParams = array_merge($defaultJobParams, $jobParams);
            /** @var CM_Jobdistribution_Job_Abstract $job */
            $job = $reflectionClass->newInstanceArgs([CM_Params::factory($jobParams)]);
            $this->_jobQueue->queue($job);
        });
    }
}
