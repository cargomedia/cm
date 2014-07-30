<?php

class CMService_KissMetrics_TrackPropertyListJob extends CM_Jobdistribution_Job_Abstract {

    protected function _execute(CM_Params $params) {
        $code = $params->getString('code');
        $identityList = $params->getArray('identityList');
        $propertyList = $params->getArray('propertyList');
        $kissMetrics = new CMService_KissMetrics_Client($code);
        $kissMetrics->setIdentityList($identityList);
        $kissMetrics->trackPropertyList($propertyList);
    }
}
