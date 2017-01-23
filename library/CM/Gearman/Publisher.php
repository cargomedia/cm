<?php

class CM_Gearman_Publisher {

    /** @var GearmanClient */
    private $_gearmanClient;

    /** @var CM_Jobdistribution_Serializer */
    private $_serializer;

    /**
     * @param GearmanClient                 $gearmanClient
     * @param CM_Jobdistribution_Serializer $serializer
     */
    public function __construct(GearmanClient $gearmanClient, CM_Jobdistribution_Serializer $serializer) {
        $this->_gearmanClient = $gearmanClient;
        $this->_serializer = $serializer;
    }

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     * @return mixed
     */
    public function run(CM_Jobdistribution_Job_Abstract $job) {
        $resultList = $this->runMultiple([$job]);
        return reset($resultList);
    }

    /**
     * @param CM_Jobdistribution_Job_Abstract[] $jobs
     * @return array
     * @throws CM_Exception
     */
    public function runMultiple(array $jobs) {
        $gearmanClient = $this->_getGearmanClient();

        $resultList = [];
        $gearmanClient->setCompleteCallback(function (GearmanTask $task) use (&$resultList) {
            $resultList[] = CM_Params::decode($task->data(), true);
        });

        $failureList = [];
        $gearmanClient->setFailCallback(function (GearmanTask $task) use (&$failureList) {
            $failureList[] = $task;
        });

        \Functional\each($jobs, function (CM_Jobdistribution_Job_Abstract $job) use ($gearmanClient) {
            $workload = CM_Params::encode($job->getParams(), true);
            $task = $gearmanClient->addTaskHigh($job->getJobName(), $workload);
            if (false === $task) {
                throw new CM_Exception('Cannot add task', null, ['jobName' => $job->getJobName()]);
            }
        });
        $gearmanClient->runTasks();

        if (count($resultList) != count($jobs)) {
            throw new CM_Exception('Job failed. Invalid results', null, [
                'jobName'         => \Functional\map($jobs, function (CM_Jobdistribution_Job_Abstract $job) {
                    return $job->getJobName();
                }),
                'countResultList' => count($resultList),
                'countJobs'       => count($jobs),
            ]);
        }
        return $resultList;
    }

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     * @throws CM_Exception
     */
    public function queue(CM_Jobdistribution_Job_Abstract $job) {
        $gearmanClient = $this->_getGearmanClient();

        $workload = $this->_serializer->serialize($job);
        $priority = $job->getPriority();
        switch ($priority) {
            case CM_Jobdistribution_Priority::HIGH:
                $gearmanClient->doHighBackground($job->getJobName(), $workload);
                break;
            case CM_Jobdistribution_Priority::NORMAL:
                $gearmanClient->doBackground($job->getJobName(), $workload);
                break;
            case CM_Jobdistribution_Priority::LOW:
                $gearmanClient->doLowBackground($job->getJobName(), $workload);
                break;
            default:
                throw new CM_Exception('Invalid priority', null, ['priority' => (string) $priority]);
        }
    }

    /**
     * @return GearmanClient
     */
    protected function _getGearmanClient() {
        return $this->_gearmanClient;
    }
}
