<?php

class CM_Maintenance_RunEventJob extends CM_Jobdistribution_Job_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    protected function _execute(CM_Params $params) {
        //$serviceManager = $this->getServiceManager();
        $serviceManager = CM_Service_Manager::getInstance(); //TODO maybe remove CM_Service_ManagerAwareInterface

        $eventName = $params->getString('event');
        $lastRuntime = $params->has('lastRuntime') ? DateTime::createFromFormat('U', $params->getInt('lastRuntime')) : null;
        $maintenance = $serviceManager->getMaintenance();
        $newRelic = $serviceManager->getNewrelic();
        $result = new CM_Clockwork_Event_Result();

        $transactionName = 'cm maintenance start: ' . $eventName;
        $newRelic->startTransaction($transactionName);
        try {
            $maintenance->runEvent($eventName, $lastRuntime);
            $result->setSuccess();
        } catch (Exception $e) {
            $result->setFailure();
            throw $e;
        } finally {
            $newRelic->endTransaction();
            $maintenance->handleClockworkEventResult($eventName, $result);
        }
    }

}
