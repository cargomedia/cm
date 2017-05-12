<?php

interface CM_Jobdistribution_QueueInterface {

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     */
    public function queue(CM_Jobdistribution_Job_Abstract $job);

    public function consume();
}
