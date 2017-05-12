<?php

interface CM_Jobdistribution_QueueInterface {

    public function consume();

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     */
    public function queue(CM_Jobdistribution_Job_Abstract $job);

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     * @return mixed
     */
    public function runSync(CM_Jobdistribution_Job_Abstract $job);

    /**
     * @param CM_Jobdistribution_Job_Abstract[] $jobs
     * @return mixed[]
     */
    public function runSyncMultiple(array $jobs);

}
