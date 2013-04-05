<?php

class CM_Site_AbstractTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
		CM_Config::get()->CM_Site_Abstract->url = 'http://www.foo.com';
		CM_Config::get()->CM_Site_Abstract->urlCdn = 'http://www.cdn.com';
		CM_Config::get()->CM_Site_Abstract->name = 'Foo';
		CM_Config::get()->CM_Site_Abstract->emailAddress = 'foo@foo.com';
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testGetEmailAddress() {
		/** @var CM_Site_Abstract $site */
		$site = $this->getMockForAbstractClass('CM_Site_Abstract');
		$this->assertEquals('foo@foo.com', $site->getEmailAddress());
	}

	public function testGetName() {
		/** @var CM_Site_Abstract $site */
		$site = $this->getMockForAbstractClass('CM_Site_Abstract');
		$this->assertEquals('Foo', $site->getName());
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
		$this->assertInstanceOf(get_class(CMTest_TH::getSiteMockMatchAll()), CM_Site_Abstract::findByRequest($request));
		$siteDefault = CM_Site_Abstract::factory();
		// Remove site mocks from the cache and from the file system
		CMTest_TH::clearCache();
		CMTest_TH::clearTmp();
		$this->assertInstanceOf(get_class($siteDefault), CM_Site_Abstract::findByRequest($request));
	}
}
