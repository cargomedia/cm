<?php

class CMService_KissMetrics_TrackPropertyListJob extends CM_Jobdistribution_Job_Abstract {

    protected function _execute(CM_Params $params) {
        $code = $params->getString('code');
        if (!$params->has('userId') && !$params->has('requestClientId')) {
            throw new CM_Exception_InvalidParam('Parameters `userId` and `requestClientId` not set');
        }
        $propertyList = $params->getArray('propertyList');
        $kissMetrics = new CMService_KissMetrics_Client($code);
        if ($params->has('userId')) {
            $userId = $params->getInt('userId');
            $kissMetrics->setUserId($userId);
        }
        if ($params->has('requestClientId')) {
            $requestClientId = $params->getInt('requestClientId');
            $kissMetrics->setRequestClientId($requestClientId);
        }
        $kissMetrics->trackPropertyList($propertyList);
    }
}
