<?php

class CMService_KissMetrics_TrackEventJob extends CM_Jobdistribution_Job_Abstract {

    protected function _execute(CM_Params $params) {
        $code = $params->getString('code');
        $userId = $params->getInt('userId');
        $eventName = $params->getString('eventName');
        $propertyList = $params->getArray('propertyList');
        $kissMetrics = new CMService_KissMetrics_Client($code);
        $kissMetrics->setUserId($userId);
        $kissMetrics->trackEvent($eventName, $propertyList);
    }
}
