<?php

class CMService_KissMetrics_TrackEventJob extends CM_Jobdistribution_Job_Abstract {

    protected function _execute(CM_Params $params) {
        $code = $params->getString('code');
        $identityList = $params->getArray('identityList');
        $eventName = $params->getString('eventName');
        $propertyList = $params->getArray('propertyList');
        $kissMetrics = new CMService_KissMetrics_Client($code);
        $kissMetrics->setIdentityList($identityList);
        $kissMetrics->trackEvent($eventName, $propertyList);
    }
}
