<?php

require_once __DIR__ . '/../../../TestCase.php';

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

	public function testGetPathByClassName() {
		$this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Test_Page_Foo_Bar_FooBar2', false);
		$this->assertSame('foo/bar/foo-bar2', CM_Page_Abstract::getPathByClassName('Test_Page_Foo_Bar_FooBar2'));
		$this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Test_Page_Index', false);
		$this->assertSame('', CM_Page_Abstract::getPathByClassName('Test_Page_Index'));
		try {
			CM_Page_Abstract::getPathByClassName('NonexistentPage');
			$this->fail('Can compute path of nonexistent page class');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
		$this->getMockForAbstractClass('CM_Model_Abstract', array(), 'InvalidClass', false);
		try {
			CM_Page_Abstract::getPathByClassName('InvalidClass');
			$this->fail('Can compute path of invalid class');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
	}
}
