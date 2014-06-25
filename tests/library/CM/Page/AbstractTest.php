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

    public function testGetLayout() {
        $site = $this->getMockBuilder('CM_Site_Abstract')->setMethods(array('getNamespaces'))->getMock();
        $site->expects($this->any())->method('getNamespaces')->will($this->returnValue(array('Foo', 'Bar')));
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
        $site = $this->getMockBuilder('CM_Site_Abstract')->setMethods(array('getNamespaces'))->getMock();
        $site->expects($this->any())->method('getNamespaces')->will($this->returnValue(array('FooBar')));
        $environment = new CM_Frontend_Environment($site);
        /** @var CM_Page_Abstract $page */
        $page = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Foo_Page_Test', false);

        $this->assertEquals('Bar_Layout_Default', get_class($page->getLayout($environment)));
    }

    public function testTrackingDisabled() {
        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Mock_Tracking');
        /** @var CM_Model_User $viewer */
        $request = new CM_Request_Get('/mock/tracking', array('host' => 'www.default.dev'));
        $response = new CM_Response_Page($request);
        $response->process();
        $js = $response->getRender()->getGlobalResponse()->getJs();

        $this->assertNotContains("_gaq.push(['_trackPageview'", $js);
        $this->assertNotContains("_kmq.push(['identify'", $js);
    }

    public function testTrackingGuest() {
        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Mock_Tracking');
        /** @var CM_Model_User $viewer */
        $request = new CM_Request_Get('/mock/tracking', array('host' => 'www.default.dev'));
        $response = new CM_Response_Page($request);
        $response->getRender()->setServiceManager($this->_getServiceManager('ga123', 'km123'));
        $response->process();
        $js = $response->getRender()->getGlobalResponse()->getJs();

        $this->assertContains("_gaq.push(['_trackPageview']);", $js);
        $this->assertNotContains("_kmq.push(['identify'", $js);
    }

    public function testTrackingViewer() {
        $viewer = $this->getMock('CM_Model_User', array('getIdRaw', 'getVisible', 'getLanguage'));
        $viewer->expects($this->any())->method('getIdRaw')->will($this->returnValue(array('id' => '1')));
        $viewer->expects($this->any())->method('getVisible')->will($this->returnValue(false));
        $viewer->expects($this->any())->method('getLanguage')->will($this->returnValue(null));
        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Mock_Tracking');
        /** @var CM_Model_User $viewer */
        $request = new CM_Request_Get('/mock/tracking', array('host' => 'www.default.dev'), null, $viewer);
        $response = new CM_Response_Page($request);
        $response->getRender()->setServiceManager($this->_getServiceManager('ga123', 'km123'));
        $response->process();
        $js = $response->getRender()->getGlobalResponse()->getJs();

        $this->assertContains("_gaq.push(['_trackPageview']);", $js);
        $this->assertContains("_kmq.push(['identify', 1]);", $js);
    }

    /**
     * @param string $codeGoogleAnalytics
     * @param string $codeKissMetrics
     * @return CM_Service_Manager
     */
    protected function _getServiceManager($codeGoogleAnalytics, $codeKissMetrics) {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('tracking-googleanalytics-test', 'CMService_GoogleAnalytics_Client', array($codeGoogleAnalytics));
        $serviceManager->register('tracking-kissmetrics-test', 'CMService_KissMetrics_Client', array($codeKissMetrics));
        $serviceManager->unregister('trackings');
        $serviceManager->register('trackings', 'CM_Service_Trackings', array(array('tracking-googleanalytics-test', 'tracking-kissmetrics-test')));
        return $serviceManager;
    }
}
