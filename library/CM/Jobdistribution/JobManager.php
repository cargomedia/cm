<?php

class CM_Jobdistribution_JobManager implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var array */
    protected $_gearmanServers;

    /**
     * @param array|null $gearmanServers
     */
    public function __construct(array $gearmanServers = null) {
        $this->_gearmanServers = (array) $gearmanServers;
    }

    /**
     * @return CM_Jobdistribution_JobWorker
     * @throws CM_Exception
     * @throws CM_Exception_Invalid
     */
    public function getWorker() {
        $worker = new CM_Jobdistribution_JobWorker($this->_gearmanServers);
        $worker->setServiceManager($this->getServiceManager());
        return $worker;
    }

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     * @throws CM_Exception
     * @return mixed
     */
    public function run(CM_Jobdistribution_Job_Abstract $job) {
        $resultList = $this->runMultiple([$job]);
        return reset($resultList);
    }

    /**
     * @param CM_Jobdistribution_Job_Abstract[] $jobList
     * @return mixed[]
     * @throws CM_Exception
     */
    public function runMultiple(array $jobList) {
        if (!$this->_getGearmanEnabled()) {
            return $this->_runMultipleWithoutGearman($jobList);
        }

        $resultList = array();
        $gearmanClient = $this->_getGearmanClient();

        $gearmanClient->setCompleteCallback(function (GearmanTask $task) use (&$resultList) {
            $resultList[] = CM_Params::decode($task->data(), true);
        });

        $failureList = array();
        $gearmanClient->setFailCallback(function (GearmanTask $task) use (&$failureList) {
            $failureList[] = $task;
        });

        foreach ($jobList as $job) {
            $workload = $this->_convertJobToWorkload($job);
            $task = $gearmanClient->addTask($job->getJobName(), $workload);
            if (false === $task) {
                throw new CM_Exception('Cannot add task `' . $job->getJobName() . '`.');
            }
        }
        $gearmanClient->runTasks();

        if (count($resultList) != count($jobList)) {
            throw new CM_Exception('Job `' . 'job-name' . '` failed (' . count($resultList) . '/' . count($jobList) . ' results).');
        }
        return $resultList;
    }

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     */
    public function queue(CM_Jobdistribution_Job_Abstract $job) {
        if (!$this->_getGearmanEnabled()) {
            $this->_runMultipleWithoutGearman([$job]);
            return;
        }
        $workload = $this->_convertJobToWorkload($job);
        $gearmanClient = $this->_getGearmanClient();
        $gearmanClient->doBackground($job->getJobName(), $workload);
    }

    /**
     * @param CM_Jobdistribution_Job_Abstract[] $jobList
     * @return mixed[]
     */
    protected function _runMultipleWithoutGearman(array $jobList) {
        $resultList = array();
        foreach ($jobList as $job) {
            $resultList[] = $this->getWorker()->execute($job);
        }
        return $resultList;
    }

    /**
     * @return GearmanClient
     * @throws CM_Exception
     */
    protected  function _getGearmanClient() {
        if (!extension_loaded('gearman')) {
            throw new CM_Exception('Missing `gearman` extension');
        }
        $gearmanClient = new GearmanClient();
        foreach ($this->_gearmanServers as $server) {
            $gearmanClient->addServer($server['host'], $server['port']);
        }
        return $gearmanClient;
    }

    /**
     * @return bool
     */
    protected function _getGearmanEnabled() {
        return count($this->_gearmanServers) > 0;
    }

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     * @return string
     */
    protected function _convertJobToWorkload(CM_Jobdistribution_Job_Abstract $job) {
        $workloadParams = array(
            'jobClassName' => get_class($job),
            'jobParams' => $job->getParams()->getParamsEncoded(),
        );
        return CM_Params::encode($workloadParams, true);
    }
}
