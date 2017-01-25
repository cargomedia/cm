<?php

interface CM_Jobdistribution_QueueInterface {

    public function publish(CM_Jobdistribution_Job_Abstract $job);

    public function consume();
}
