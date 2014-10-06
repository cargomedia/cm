<?php

class CM_Jobdistribution_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @keepalive
     */
    public function startWorker() {
        $worker = new CM_Jobdistribution_JobWorker();
        foreach (CM_Jobdistribution_Job_Abstract::getClassChildren() as $jobClassName) {
            $job = new $jobClassName();
            $worker->registerJob($job);
        }
        $worker->run();
    }

    public static function getPackageName() {
        return 'job-distribution';
    }
}
