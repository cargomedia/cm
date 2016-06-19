<?php

class CM_Geo_PointTest extends CMTest_TestCase {

    public function testConstruct() {
        $point = new CM_Geo_Point(11.2, 13.2);
        $this->assertSame(11.2, $point->getLatitude());
        $this->assertSame(13.2, $point->getLongitude());
    }

    public function testGetSetLatitude() {
        $point = new CM_Geo_Point(11.2, 13.2);
        $point->setLatitude(-14.3);
        $this->assertSame(-14.3, $point->getLatitude());
    }

    public function testGetSetLatitudeZero() {
        $point = new CM_Geo_Point(11.2, 13.2);
        $point->setLatitude(0);
        $this->assertSame(0.0, $point->getLatitude());
    }

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function testGetSetLatitudeOutOfRange() {
        $point = new CM_Geo_Point(11.2, 13.2);
        $point->setLatitude(300);
    }

    public function testGetSetLongitude() {
        $point = new CM_Geo_Point(11.2, 13.2);
        $point->setLongitude(-14.3);
        $this->assertSame(-14.3, $point->getLongitude());
    }

    public function testGetSetLongitudeZero() {
        $point = new CM_Geo_Point(11.2, 13.2);
        $point->setLongitude(0);
        $this->assertSame(0.0, $point->getLongitude());
    }

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function testGetSetLongitudeOutOfRange() {
        $point = new CM_Geo_Point(11.2, 13.2);
        $point->setLongitude(300);
    }

    public function testCalculateDistanceTo() {
        $berlinPoint = new CM_Geo_Point(52.523403, 13.411400);
        $newYorkPoint = new CM_Geo_Point(40.71278, -74.00594);
        $this->assertEquals(6380800, $berlinPoint->calculateDistanceTo($newYorkPoint), null, 10000);
    }

    public function testToArray() {
        $point = new CM_Geo_Point(51.3, 52.4);
        $this->assertSame(['latitude' => 51.3, 'longitude' => 52.4], $point->toArray());
    }

    public function testFromArray() {
        $data = ['latitude' => 51.3, 'longitude' => 52.4];
        $point = new CM_Geo_Point(51.3, 52.4);
        $this->assertEquals($point, CM_Geo_Point::fromArray($data));
    }
}
