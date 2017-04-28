<?php

class CM_Page_AbstractTest extends CMTest_TestCase {

    public function testGetCanTrackPageView() {
        $page = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Test_Page_GetCanTrackPageView', false);
        /** @var CM_Page_Abstract $page */
        $this->assertSame(true, $page->getCanTrackPageView());
    }

    public function testGetPathVirtualPageView() {
        $page = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Test_Page_GetPathVirtualPageView', false);
        /** @var CM_Page_Abstract $page */
        $this->assertSame(null, $page->getPathVirtualPageView());
    }

    public function testGetClassnameByPath() {
        $site = $this->getMockBuilder('CM_Site_Abstract')->setMethods(array('getModules'))->getMock();
        $site->expects($this->any())->method('getModules')->will($this->returnValue(array('Foo', 'Bar')));
        /** @var CM_Site_Abstract $site */
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));

        $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Bar_Page_Test', false);
        $this->assertEquals('Bar_Page_Test', CM_Page_Abstract::getClassnameByPath($render, '/test'));

        $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Foo_Page_Test', false);
        $this->assertEquals('Foo_Page_Test', CM_Page_Abstract::getClassnameByPath($render, '/test'));
    }

    public function testGetClassnamebyPathWithMultipleSlashes() {
        /** @var CM_Site_Abstract|\Mocka\AbstractClassTrait $site */
        $site = $this->mockObject(CM_Site_Abstract::class);
        $site->mockMethod('getModules')->set(['Foo']);
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));

        new \Mocka\ClassMock('Foo_Page_Foo_Bar_Test', CM_Page_Abstract::class);
        $this->assertEquals('Foo_Page_Foo_Bar_Test', CM_Page_Abstract::getClassnameByPath($render, '//foo///bar///////test'));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage The class was not found in any namespace.
     */
    public function testGetClassnameByPathNotExists() {
        $site = $this->getMockBuilder('CM_Site_Abstract')->setMethods(array('getModules'))->getMock();
        $site->expects($this->any())->method('getModules')->will($this->returnValue(array('FooBar')));
        /** @var CM_Site_Abstract $site */
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));

        CM_Page_Abstract::getClassnameByPath($render, '/test');
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
        $this->assertEquals('Bar_Layout_Default', $page->getLayout($environment));

        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'Foo_Layout_Default', false);
        $this->assertEquals('Foo_Layout_Default', $page->getLayout($environment));
    }

    public function testGetLayoutNotExists() {
        $site = $this->getMockBuilder('CM_Site_Abstract')->setMethods(array('getModules'))->getMock();
        $site->expects($this->any())->method('getModules')->will($this->returnValue(array('FooBar')));
        $environment = new CM_Frontend_Environment($site);
        /** @var CM_Page_Abstract $page */
        $page = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Foo_Page_Test', false);

        $exception = $this->catchException(function () use ($page,$environment) {
            $page->getLayout($environment);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Layout is not defined in any namespace', $exception->getMessage());
        $this->assertSame(['layoutName' => 'Default'], $exception->getMetaInfo());
    }
}
