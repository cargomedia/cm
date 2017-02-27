<?php

class CM_Jobdistribution_Cli extends CM_Cli_Runnable_Abstract {

    public function startWorker() {
        $worker = new CM_Jobdistribution_JobWorker(1000);
        $worker->setServiceManager($this->getServiceManager());
        foreach (CM_Jobdistribution_Job_Abstract::getClassChildren() as $jobClassName) {
            /** @var CM_Jobdistribution_Job_Abstract $job */
            $job = new $jobClassName();
            if ($job instanceof CM_Service_ManagerAwareInterface) {
                /** @var CM_Service_ManagerAwareInterface $job */
                $job->setServiceManager($this->getServiceManager());
            }
            $worker->registerJob($job);
        }
        $worker->run();
    }

    public static function getPackageName() {
        return 'job-distribution';
    }
}
