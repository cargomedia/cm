<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Site_AbstractTest extends TestCase {

	private static $_configBackup;

	public static function setUpBeforeClass() {
		self::$_configBackup = CM_Config::get();
		CM_Config::get()->CM_Site_Abstract = new stdClass();
		CM_Config::get()->CM_Site_Abstract->url = 'http://www.foo.com';
		CM_Config::get()->CM_Site_Abstract->urlCdn = 'http://www.cdn.com';
	}

	public static function tearDownAfterClass() {	
		TH::clearEnv();
		CM_Config::set(self::$_configBackup);
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

	public function testFindAll() {
		$this->assertSame(array('CM_Site_CM'), CM_Site_Abstract::findAll());
	}

	public function testFindByRequest() {
		$request = new CM_Request_Get('/test');
		$this->assertInstanceOf('CM_Site_CM', CM_Site_Abstract::findByRequest($request));
	}
}
