<?php

require_once dirname(__FILE__) . '/../../../TestCase.php';

class CM_Page_AbstractTest extends TestCase {

	public static function setUpBeforeClass() {
	}


	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testGetPath() {
		$request = new CM_Request_Get('/test');

		$page = $this->getMockForAbstractClass('CM_Page_Abstract', array($request));
		$path = $page->getPath();

		$this->assertEquals('/abstract/', substr($path, 0, 10));
	}
}
