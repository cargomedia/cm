<?php

class CM_Service_TrackingsTest extends CMTest_TestCase {

    public function testConstructor() {
        $siteToTrackingsMap = [
            'CM_Site_Abstract' => [
                'foo',
                'bar' => 'baz'
            ],
        ];
        $serviceTrackings = new CM_Service_Trackings($siteToTrackingsMap);
        $this->assertSame($siteToTrackingsMap, $serviceTrackings->getSiteToTrackingsMap());
    }

    public function testGetTrackingServiceNameList() {
        $siteMockClass = $this->mockClass('CM_Site_Abstract');
        $siteMockClass->mockStaticMethod('getClassHierarchyStatic')->set([
            'CM_Site_Mock',
            'CM_Site_GrandMock',
            'CM_Site_Abstract',
        ]);
        $siteMock = $siteMockClass->newInstanceWithoutConstructor();

        $siteGrandMockClass = $this->mockClass('CM_Site_Abstract');
        $siteGrandMockClass->mockStaticMethod('getClassHierarchyStatic')->set([
            'CM_Site_GrandMock',
            'CM_Site_Abstract',
        ]);
        $siteGrandMockClass = $siteGrandMockClass->newInstanceWithoutConstructor();

        $serviceTracking = new CM_Service_Trackings([
            'CM_Site_Abstract'  => [
                'foo',
                'b' => 'bar',
                'baz',
            ],
            'CM_Site_GrandMock' => [
                'b' => 'bar2',
            ],
            'CM_Site_Mock'      => [
                'bb' => 'barBaz',
                'foo',
            ]
        ]);

        $serviceNamesList = $this->callProtectedMethod($serviceTracking, '_getTrackingServiceNameList', [get_class($siteMock)]);
        $this->assertSame([
            'foo',
            'bar2',
            'baz',
            'barBaz',
        ], $serviceNamesList);

        $serviceNamesList = $this->callProtectedMethod($serviceTracking, '_getTrackingServiceNameList', [get_class($siteGrandMockClass)]);
        $this->assertSame([
            'foo',
            'bar2',
            'baz',
        ], $serviceNamesList);

        $serviceNamesList = $this->callProtectedMethod($serviceTracking, '_getTrackingServiceNameList', ['CM_Site_Abstract']);
        $this->assertSame([
            'foo',
            'bar',
            'baz',
        ], $serviceNamesList);
    }

    public function testGetTrackingServiceList() {
        $serviceTrackings = new CM_Service_Trackings(['foo' => 'bar']);
        $exception = $this->catchException(function () use ($serviceTrackings) {
            $serviceTrackings->getTrackingServiceList('Foo');
        });
        
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('`Foo` is not a child of CM_Site_Abstract', $exception->getMessage());
    }
}



