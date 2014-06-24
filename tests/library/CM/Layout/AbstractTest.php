<?php

class CM_Layout_AbstractTest extends CMTest_TestCase {

    public function tearDown() {
        $this->_clearTracking();
    }

    public function testTrackingDisabled() {
        $site = $this->getMockSite('CM_Site_Abstract');
        $render = new CM_Frontend_Render($site);
        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $pageMock = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Mock' . uniqid());
        /** @var CM_Page_Abstract $pageMock */
        $renderAdapter = new CM_RenderAdapter_Layout($render, $pageMock);
        $html = $renderAdapter->fetch();

        $this->assertNotContains('var _gaq = _gaq || [];', $html);
        $this->assertNotContains('var _kmq = _kmq || [];', $html);
    }

    public function testTrackingGuest() {
        $this->_configureTracking('ga123', 'km123');

        $siteMock = $this->getMockSite('CM_Site_Abstract', null, array('url' => 'http://www.example.com'));
        $render = new CM_Frontend_Render($siteMock);
        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $pageMock = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Mock' . uniqid());
        /** @var CM_Page_Abstract $pageMock */
        $renderAdapter = new CM_RenderAdapter_Layout($render, $pageMock);
        $html = $renderAdapter->fetch();

        $this->assertContains('var _gaq = _gaq || [];', $html);
        $this->assertContains("_gaq.push(['_setAccount', 'ga123']);", $html);
        $this->assertContains("_gaq.push(['_setDomainName', 'www.example.com']);", $html);
        $this->assertNotContains("_gaq.push(['_trackPageview'", $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $this->assertNotContains("_kmq.push(['identify'", $html);
    }

    public function testTrackingViewer() {
        $this->_configureTracking('ga123', 'km123');

        $site = $this->getMockSite('CM_Site_Abstract', null, array('url' => 'http://www.example.com'));
        $viewer = $this->getMock('CM_Model_User', array('getIdRaw', 'getVisible'));
        $viewer->expects($this->any())->method('getIdRaw')->will($this->returnValue(array('id' => '1')));
        $viewer->expects($this->any())->method('getVisible')->will($this->returnValue(false));
        /** @var CM_Model_User $viewer */
        $render = new CM_Frontend_Render($site, $viewer);
        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $pageMock = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Mock' . uniqid());
        /** @var CM_Page_Abstract $pageMock */
        $renderAdapter = new CM_RenderAdapter_Layout($render, $pageMock);
        $html = $renderAdapter->fetch();

        $this->assertContains('var _gaq = _gaq || [];', $html);
        $this->assertContains("_gaq.push(['_setAccount', 'ga123']);", $html);
        $this->assertContains("_gaq.push(['_setDomainName', 'www.example.com']);", $html);
        $this->assertNotContains("_gaq.push(['_trackPageview'", $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $this->assertNotContains("_kmq.push(['identify'", $html);
    }

    protected function _configureTracking($codeGoogleAnalytics, $codeKissMetrics) {
        $serviceManager = CM_Service_Manager::getInstance();
        $serviceManager->unregister('tracking-googleanalytics');
        $serviceManager->register('tracking-googleanalytics', 'CMService_GoogleAnalytics_Client', array($codeGoogleAnalytics));
        $serviceManager->unregister('tracking-kissmetrics');
        $serviceManager->register('tracking-kissmetrics', 'CMService_KissMetrics_Client', array($codeKissMetrics));
        $serviceManager->unregister('trackings');
        $serviceManager->register('trackings', 'CM_Service_Trackings', array(array('tracking-googleanalytics', 'tracking-kissmetrics')));
    }

    protected function _clearTracking() {
        $serviceManager = CM_Service_Manager::getInstance();
        $serviceManager->unregister('trackings');
        $serviceManager->registerWithArray('trackings', CM_Config::get()->services['trackings']);
    }
}
