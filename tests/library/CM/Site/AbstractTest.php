<?php

class CM_Site_AbstractTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
		CM_Config::get()->CM_Site_Abstract = new stdClass();
		CM_Config::get()->CM_Site_Abstract->url = 'http://www.foo.com';
		CM_Config::get()->CM_Site_Abstract->urlCdn = 'http://www.cdn.com';
	}

	public static function tearDownAfterClass() {	
		CMTest_TH::clearEnv();
	}

	public function testGetUrl() {
		/** @var CM_Site_Abstract $site */
		$site = $this->getMockForAbstractClass('CM_Site_Abstract');
		$this->assertEquals('http://www.foo.com', $site->getUrl());
	}

	public function testGetUrlCdn() {
		/** @var CM_Site_Abstract $site */
		$site = $this->getMockForAbstractClass('CM_Site_Abstract');
		$this->assertEquals('http://www.cdn.com', $site->getUrlCdn());
	}

	public function testFindByRequest() {
		$request = new CM_Request_Get('/test');
		$this->assertInstanceOf('CM_Site_CM', CM_Site_Abstract::findByRequest($request));
	}
}
