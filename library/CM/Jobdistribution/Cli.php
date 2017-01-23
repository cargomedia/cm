<?php

class CM_Jobdistribution_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @keepalive
     */
    public function startWorker() {
        $jobQueue = $this->getServiceManager()->getJobQueue();
        $jobQueue->consume();
    }

    public static function getPackageName() {
        return 'job-distribution';
    }
}
