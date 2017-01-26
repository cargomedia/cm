<?php


interface CM_Jobdistribution_RPCInterface {

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     * @return mixed
     */
    public function run(CM_Jobdistribution_Job_Abstract $job);

    /**
     * @param CM_Jobdistribution_Job_Abstract[] $jobs
     * @return mixed[]
     */
    public function runMultiple(array $jobs);

    public function consume();
}
