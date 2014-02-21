<?php

class CM_Page_AbstractTest extends CMTest_TestCase {

	public function testGetClassnameByPath() {
		$site = $this->getMockBuilder('CM_Site_Abstract')->setMethods(array('getNamespaces'))->getMock();
		$site->expects($this->any())->method('getNamespaces')->will($this->returnValue(array('Foo', 'Bar')));

		$this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Bar_Page_Test', false);
		$this->assertEquals('Bar_Page_Test', CM_Page_Abstract::getClassnameByPath($site, '/test'));

		$this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Foo_Page_Test', false);
		$this->assertEquals('Foo_Page_Test', CM_Page_Abstract::getClassnameByPath($site, '/test'));
	}

	/**
	 * @expectedException CM_Exception_Invalid
	 * @expectedExceptionMessage page `Test` is not defined in any namespace
	 */
	public function testGetClassnameByPathNotExists() {
		$site = $this->getMockBuilder('CM_Site_Abstract')->setMethods(array('getNamespaces'))->getMock();
		$site->expects($this->any())->method('getNamespaces')->will($this->returnValue(array('FooBar')));

		CM_Page_Abstract::getClassnameByPath($site, '/test');
	}

	public function testGetPath() {
		$this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Test_Page_Foo_Bar_FooBar2', false);
		$this->assertSame('/foo/bar/foo-bar2?foo=1&bar=2', Test_Page_Foo_Bar_FooBar2::getPath(array('foo' => 1, 'bar' => 2)));
		$this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Test_Page_Index', false);
		$this->assertSame('/', Test_Page_Index::getPath());
	}
}
