<?php

class CM_Gearman_Worker extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

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
        $this->_gearmanWorker->addFunction($jobName, [$this, 'runJob']);
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
                $workFailed = !$this->_gearmanWorker->work();
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
    public function runJob(GearmanJob $gearmanJob) {
        $job = $this->_convertGearmanJobToJob($gearmanJob);
        $result = CM_Service_Manager::getInstance()->getNewrelic()->runAsTransaction('CM Job: ' . $job->getJobName(), function() use ($job) {
            return $job->execute();
        });
        return $this->_serializer->serializeJobResult($result);
    }

    /**
     * @param GearmanJob $gearmanJob
     * @return CM_Jobdistribution_Job_Abstract
     * @throws CM_Exception_Invalid
     * @throws CM_Exception_Nonexistent
     */
    protected function _convertGearmanJobToJob(GearmanJob $gearmanJob) {
        $workload = $gearmanJob->workload();
        $params = CM_Params::factory(CM_Params::jsonDecode($workload), true);
        if ($params->has('jobClassName')) {
            $job = $this->_serializer->unserializeJob($workload);
        } else {
            $jobClassName = $gearmanJob->functionName();
            $job = new $jobClassName($params);
        }
        if ($job instanceof CM_Service_ManagerAwareInterface) {
            $job->setServiceManager($this->getServiceManager());
        }
        return $job;
    }
}
