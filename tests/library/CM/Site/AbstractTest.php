<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Site_AbstractTest extends TestCase {

	private static $_configBackup;

	public static function setUpBeforeClass() {
		self::$_configBackup = CM_Config::get();
		CM_Config::get()->CM_Site_Abstract = new stdClass();
		CM_Config::get()->CM_Site_Abstract->urlRoot = 'http://www.foo.com';
		CM_Config::get()->CM_Site_Abstract->urlCdn = 'http://www.cdn.com';
	}

	public static function tearDownAfterClass() {	
		TH::clearEnv();
		CM_Config::set(self::$_configBackup);
	}

	public function testGetUrlRoot() {
		/** @var CM_Site_Abstract $site */
		$site = $this->getMockForAbstractClass('CM_Site_Abstract');
		$this->assertEquals('http://www.foo.com', $site->getUrlRoot());
	}

	public function testGetUrlCdn() {
		/** @var CM_Site_Abstract $site */
		$site = $this->getMockForAbstractClass('CM_Site_Abstract');
		$this->assertEquals('http://www.cdn.com', $site->getUrlCdn());
	}
}
