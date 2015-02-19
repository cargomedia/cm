<?php

class CMService_GoogleAnalytics_MeasurementProtocol_SendHitJob extends CM_Jobdistribution_Job_Abstract {

    protected function _execute(CM_Params $params) {
        $propertyId = $params->getString('propertyId');
        $parameterList = $params->getArray('parameterList');

        $client = new CMService_GoogleAnalytics_MeasurementProtocol_Client($propertyId);
        $client->_submitHit($parameterList);
    }
}
