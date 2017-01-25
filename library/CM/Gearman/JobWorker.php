<?php

class CM_Gearman_JobWorker extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var GearmanWorker */
    private $_gearmanWorker;

    /** @var int */
    private $_jobLimit;
    
    /** @var CM_Jobdistribution_JobSerializer */
    private $_serializer;

    /**
     * @param GearmanWorker                    $worker
     * @param CM_Jobdistribution_JobSerializer $serializer
     * @param int                              $jobLimit
     */
    public function __construct(GearmanWorker $worker, CM_Jobdistribution_JobSerializer $serializer, $jobLimit) {
        $this->_gearmanWorker = $worker;
        $this->_serializer = $serializer;
        $this->_jobLimit = (int) $jobLimit;
    }

    /**
     * @param string $jobName
     */
    public function registerJob($jobName) {
        $this->_getGearmanWorker()->addFunction($jobName, [$this, 'executeGearmanJob']);
    }

    public function run() {
        $jobsRun = 0;
        while (true) {
            if ($jobsRun >= $this->_jobLimit) {
                return;
            }
            $workFailed = false;
            try {
                $jobsRun++;
                CM_Cache_Storage_Runtime::getInstance()->flush();
                $workFailed = !$this->_getGearmanWorker()->work();
            } catch (Exception $ex) {
                $this->getServiceManager()->getLogger()->addMessage('Worker failed', CM_Log_Logger::exceptionToLevel($ex), (new CM_Log_Context())->setException($ex));
            }
            if ($workFailed) {
                throw new CM_Exception_Invalid('Worker failed');
            }
        }
    }

    /**
     * @param GearmanJob $gearmanJob
     * @return string
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
        CM_Service_Manager::getInstance()->getNewrelic()->startTransaction('CM Job: ' . $this->_getClassName());
        try {
            $params = $job->getParams();
            $returnValue = $job->execute($params);
            CM_Service_Manager::getInstance()->getNewrelic()->endTransaction();
            return $returnValue;
        } catch (Exception $ex) {
            CM_Service_Manager::getInstance()->getNewrelic()->endTransaction();
            throw $ex;
        }
    }

    /**
     * @return GearmanWorker
     * @throws CM_Exception
     */
    protected function _getGearmanWorker() {
        if (!$this->_gearmanWorker) {
            if (!extension_loaded('gearman')) {
                throw new CM_Exception('Missing `gearman` extension');
            }
            $this->_gearmanWorker = new GearmanWorker();
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
        $job = $this->_serializer->unserialize($workload);
        if ($job instanceof CM_Service_ManagerAwareInterface) {
            $job->setServiceManager($this->getServiceManager());
        }
        return $job;
    }
}
