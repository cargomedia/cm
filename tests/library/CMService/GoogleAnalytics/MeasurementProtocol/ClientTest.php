<?php

class CMService_GoogleAnalytics_MeasurementProtocol_ClientTest extends CMTest_TestCase {

    public function testSubmitHit() {
        $clientMock = $this->mockClass('CMService_GoogleAnalytics_MeasurementProtocol_Client');
        $submitRequestMock = $clientMock->mockMethod('_submitRequest')->set(function ($parameterList) {
            $this->assertSame(['bar' => 12, 'v' => 1, 'tid' => 'prop1', 't' => 'foo'], $parameterList);
        });
        /** @var CMService_GoogleAnalytics_MeasurementProtocol_Client $client */
        $client = $clientMock->newInstance(['prop1']);

        $client->_submitHit('foo', ['bar' => 12]);
        $this->assertSame(1, $submitRequestMock->getCallCount());
    }
}
