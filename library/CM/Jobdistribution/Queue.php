<?php

interface CM_Jobdistribution_Queue {

    public function publish(CM_Jobdistribution_Job_Abstract $job);

    public function consume();
}
