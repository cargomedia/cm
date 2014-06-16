<?php

class CM_Page_AbstractTest extends CMTest_TestCase {

    public function testGetClassnameByPath() {
        $site = $this->getMockBuilder('CM_Site_Abstract')->setMethods(array('getModules'))->getMock();
        $site->expects($this->any())->method('getModules')->will($this->returnValue(array('Foo', 'Bar')));

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
        $site = $this->getMockBuilder('CM_Site_Abstract')->setMethods(array('getModules'))->getMock();
        $site->expects($this->any())->method('getModules')->will($this->returnValue(array('FooBar')));

        CM_Page_Abstract::getClassnameByPath($site, '/test');
    }

    public function testGetPath() {
        $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Test_Page_Foo_Bar_FooBar2', false);
        $this->assertSame('/foo/bar/foo-bar2?foo=1&bar=2', Test_Page_Foo_Bar_FooBar2::getPath(array('foo' => 1, 'bar' => 2)));
        $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Test_Page_Index', false);
        $this->assertSame('/', Test_Page_Index::getPath());
    }

    public function testGetLayout() {
        $site = $this->getMockBuilder('CM_Site_Abstract')->setMethods(array('getModules'))->getMock();
        $site->expects($this->any())->method('getModules')->will($this->returnValue(array('Foo', 'Bar')));
        /** @var CM_Page_Abstract $page */
        $environment = new CM_Frontend_Environment($site);
        $page = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Foo_Page_Test', false);

        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'Bar_Layout_Default', false);
        $this->assertEquals('Bar_Layout_Default', get_class($page->getLayout($environment)));

        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'Foo_Layout_Default', false);
        $this->assertEquals('Foo_Layout_Default', get_class($page->getLayout($environment)));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage layout `Default` is not defined in any namespace
     */
    public function testGetLayoutNotExists() {
        $site = $this->getMockBuilder('CM_Site_Abstract')->setMethods(array('getModules'))->getMock();
        $site->expects($this->any())->method('getModules')->will($this->returnValue(array('FooBar')));
        $environment = new CM_Frontend_Environment($site);
        /** @var CM_Page_Abstract $page */
        $page = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Foo_Page_Test', false);

        $this->assertEquals('Bar_Layout_Default', get_class($page->getLayout($environment)));
    }
}
