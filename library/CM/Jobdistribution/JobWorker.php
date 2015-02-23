<?php

class CM_Jobdistribution_JobWorker extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var GearmanWorker */
    private $_gearmanWorker;

    /** @var array */
    private $_gearmanServers;

    /**
     * @param array|null $gearmanServers
     */
    public function __construct(array $gearmanServers = null) {
        $this->_gearmanServers = (array) $gearmanServers;
    }

    /**
     * @param string $jobName
     */
    public function registerJob($jobName) {
        $this->_getGearmanWorker()->addFunction($jobName, array($this, 'executeGearmanJob'));
    }

    public function run() {
        while (true) {
            $workFailed = false;
            try {
                $workFailed = !$this->_getGearmanWorker()->work();
            } catch (Exception $ex) {
                $this->_handleException($ex);
            }
            if ($workFailed) {
                throw new CM_Exception_Invalid('Worker failed');
            }
        }
    }

    /**
     * @param GearmanJob $gearmanJob
     * @return string
     * @throws CM_Exception_Nonexistent
     */
    public function executeGearmanJob(GearmanJob $gearmanJob) {
        $job = $this->_convertGearmanJobToJob($gearmanJob);
        $result = $this->execute($job);
        return CM_Params::encode($result, true);
    }

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     * @return mixed
     * @throws Exception
     */
    public function execute(CM_Jobdistribution_Job_Abstract $job) {
        CMService_Newrelic::getInstance()->startTransaction('CM Job: ' . $job->getJobName());
        try {
            $result = $job->execute();
            CMService_Newrelic::getInstance()->endTransaction();
            return $result;
        } catch (Exception $ex) {
            CMService_Newrelic::getInstance()->endTransaction();
            throw $ex;
        }
    }

    /**
     * @param Exception $exception
     */
    protected function _handleException(Exception $exception) {
        CM_Bootloader::getInstance()->getExceptionHandler()->handleException($exception);
    }

    /**
     * @return GearmanWorker
     * @throws CM_Exception
     */
    protected function _getGearmanWorker() {
        if (null === $this->_gearmanWorker) {
            if (!extension_loaded('gearman')) {
                throw new CM_Exception('Missing `gearman` extension');
            }
            $gearmanWorker = new GearmanWorker();
            foreach ($this->_gearmanServers as $server) {
                $gearmanWorker->addServer($server['host'], $server['port']);
            }
            $this->_gearmanWorker = $gearmanWorker;
        }
        return $this->_gearmanWorker;
    }

    /**
     * @param GearmanJob $gearmanJob
     * @return CM_Jobdistribution_Job_Abstract
     * @throws CM_Exception_Invalid
     * @throws CM_Exception_Nonexistent
     */
    protected function _convertGearmanJobToJob(GearmanJob $gearmanJob) {
        $workload = $gearmanJob->workload();
        try {
            $workloadParams = CM_Params::jsonDecode($workload);
        } catch (CM_Exception_Nonexistent $ex) {
            throw new CM_Exception_Nonexistent("Cannot decode workload `{$ex->getMessage()}`, workload: `${workload}'", null, null, CM_Exception::WARN);
        }
        $jobClassName = $workloadParams['jobClassName'];
        $params = $workloadParams['jobParams'];
        $job = new $jobClassName($params);
        if ($job instanceof CM_Service_ManagerAwareInterface) {
            $job->setServiceManager($this->getServiceManager());
        }
        return $job;
    }
}
