<?php

class CMService_GoogleAnalytics_MeasurementProtocol_ClientTest extends CMTest_TestCase {

    public function testSubmitHit() {
        $clientMock = $this->mockClass('CMService_GoogleAnalytics_MeasurementProtocol_Client');
        $submitRequestMock = $clientMock->mockMethod('_submitRequest')->set(function ($parameterList) {
            $this->assertSame([
                'foo' => 12,
                'v'   => 1,
                'tid' => 'prop1',
            ], $parameterList);
        });
        /** @var CMService_GoogleAnalytics_MeasurementProtocol_Client $client */
        $client = $clientMock->newInstance(['prop1']);

        $client->_submitHit(['foo' => 12]);
        $this->assertSame(1, $submitRequestMock->getCallCount());
    }

    public function testTrackEvent() {
        $clientMock = $this->mockClass('CMService_GoogleAnalytics_MeasurementProtocol_Client');
        $submitRequestMock = $clientMock->mockMethod('trackHit')->set(function ($parameterList) {
            $this->assertSame([
                'ec' => 'MyCategory',
                'ea' => 'MyAction',
                'el' => 'MyLabel',
                'ev' => 123,
                't'  => 'event',
            ], $parameterList);
        });
        /** @var CMService_GoogleAnalytics_MeasurementProtocol_Client $client */
        $client = $clientMock->newInstance(['prop1']);

        $client->trackEvent([
            'eventCategory' => 'MyCategory',
            'eventAction'   => 'MyAction',
            'eventLabel'    => 'MyLabel',
            'eventValue'    => 123,
        ]);
        $this->assertSame(1, $submitRequestMock->getCallCount());
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Unknown parameter
     */
    public function testTrackEventInvalidParam() {
        $clientMock = $this->mockClass('CMService_GoogleAnalytics_MeasurementProtocol_Client');
        /** @var CMService_GoogleAnalytics_MeasurementProtocol_Client $client */
        $client = $clientMock->newInstance(['prop1']);

        $client->trackEvent([
            'foo' => 12,
        ]);
    }
}
