<?php

class CMService_KissMetrics_TrackEventJob extends CM_Jobdistribution_Job_Abstract {

    protected function _execute(CM_Params $params) {
        $code = $params->getString('code');
        if (!$params->has('userId') && !$params->has('clientId')) {
            throw new CM_Exception_InvalidParam('Parameters `userId` and `clientId` not set');
        }
        $eventName = $params->getString('eventName');
        $propertyList = $params->getArray('propertyList');
        $kissMetrics = new CMService_KissMetrics_Client($code);
        if ($params->has('userId')) {
            $userId = $params->getInt('userId');
            $kissMetrics->setUserId($userId);
        }
        if ($params->has('clientId')) {
            $clientId = $params->getInt('clientId');
            $kissMetrics->setClientId($clientId);
        }
        $kissMetrics->trackEvent($eventName, $propertyList);
    }
}
