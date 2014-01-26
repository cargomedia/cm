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
}
