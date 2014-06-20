<?php

class CMService_KissMetrics_TrackEventJob extends CM_Jobdistribution_Job_Abstract {

    protected function _execute(CM_Params $params) {
        $code = $params->getString('code');
        $userId = $params->getInt('userId');
        $event = $params->getString('event');
        $propertyList = $params->getArray('propertyList');
        KM::init($code);
        KM::identify($userId);
        KM::record($event, $propertyList);
    }
}
