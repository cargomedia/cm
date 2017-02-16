<?php

final class CM_EventHandler_EventHandler {

    use CM_EventHandler_EventHandlerTrait;

    /**
     * @param string                          $event
     * @param CM_Jobdistribution_Job_Abstract $job
     * @param array                           $defaultJobParams
     */
    public function bindJob($event, CM_Jobdistribution_Job_Abstract $job, array $defaultJobParams = null) {
        $event = (string) $event;
        $defaultJobParams = (array) $defaultJobParams;
        $this->bind($event, function (array $jobParams = null) use ($job, $defaultJobParams) {
            $jobParams = (array) $jobParams;
            $jobParams = array_merge($defaultJobParams, $jobParams);
            $job->queue(CM_Params::factory($jobParams), false);
        });
    }
}
