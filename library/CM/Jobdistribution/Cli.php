<?php

class CM_Jobdistribution_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @keepalive
     */
    public function startWorker() {
        $worker = CM_Service_Manager::getInstance()->getJobManager()->getWorker();
        foreach (CM_Jobdistribution_Job_Abstract::getClassChildren() as $jobClassName) {
            $worker->registerJob($jobClassName);
        }
        $worker->run();
    }

    public static function getPackageName() {
        return 'job-distribution';
    }
}
