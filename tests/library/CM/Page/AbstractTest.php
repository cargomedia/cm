<?php

require_once __DIR__ . '/../../../TestCase.php';

class CM_Page_AbstractTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testGetPath() {
		$this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Test_Page_Foo_Bar_FooBar2', false);
		$this->assertSame('foo/bar/foo-bar2?foo=1&bar=2', Test_Page_Foo_Bar_FooBar2::getPath(array('foo' => 1, 'bar' => 2)));
		$this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Test_Page_Index', false);
		$this->assertSame('', Test_Page_Index::getPath());
	}
}
