<?php

class CMService_KissMetrics_TrackEventJob extends CM_Jobdistribution_Job_Abstract {

    protected function _execute(CM_Params $params) {
        $userId = $params->getInt('userId');
        $eventName = $params->getString('eventName');
        $propertyList = $params->getArray('propertyList');
        /** @var CMService_KissMetrics_Client $kissMetricsClient */
        $kissMetricsClient = CM_Service_Manager::getInstance()->get('tracking-kissmetrics');
        $kissMetricsClient->setUserId($userId);
        $kissMetricsClient->trackEvent($eventName, $propertyList);
    }
}
