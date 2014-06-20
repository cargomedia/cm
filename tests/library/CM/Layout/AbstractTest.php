<?php

class CM_Layout_AbstractTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearConfig();
    }

    public function testTrackingDisabled() {
        $site = $this->getMockSite('CM_Site_Abstract');
        $render = new CM_Frontend_Render($site);
        $layoutMock = $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $pageMock = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Mock' . uniqid());
        /** @var CM_Page_Abstract $pageMock */
        $renderAdapter = new CM_RenderAdapter_Layout($render, $pageMock);
        $html = new CMTest_TH_Html($renderAdapter->fetch());

        $this->assertNotContains('var _gaq = _gaq || [];', $html->getHtml());
        $this->assertNotContains('var _kmq = _kmq || [];', $html->getHtml());
    }

    public function testTrackingGuest() {
        $config = CM_Config::get();
        $config->CM_Tracking_Abstract->enabled = true;
        $config->CM_Tracking_Abstract->code = 'ga123';

        $serviceManager = CM_Service_Manager::getInstance();
        $serviceManager->unregister('kissmetrics');
        $serviceManager->register('kissmetrics', 'CMService_KissMetrics_Client', array('km123'));

        $site = $this->getMockSite('CM_Site_Abstract', null, array('url' => 'http://www.example.com'));
        $render = new CM_Frontend_Render($site);
        $layoutMock = $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $pageMock = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Mock' . uniqid());
        /** @var CM_Page_Abstract $pageMock */
        $renderAdapter = new CM_RenderAdapter_Layout($render, $pageMock);
        $html = new CMTest_TH_Html($renderAdapter->fetch());

        $this->assertContains('var _gaq = _gaq || [];', $html->getHtml());
        $this->assertContains("_gaq.push(['_setAccount', 'ga123']);", $html->getHtml());
        $this->assertContains("_gaq.push(['_setDomainName', 'www.example.com']);", $html->getHtml());
        $this->assertContains('var _kmq = _kmq || [];', $html->getHtml());
        $this->assertContains("var _kmk = _kmk || 'km123';", $html->getHtml());
        $this->assertNotContains("_kmq.push(['identify',", $html->getHtml());
    }

    public function testTrackingViewer() {
        $config = CM_Config::get();
        $config->CM_Tracking_Abstract->enabled = true;
        $config->CM_Tracking_Abstract->code = 'ga123';

        $serviceManager = CM_Service_Manager::getInstance();
        $serviceManager->unregister('kissmetrics');
        $serviceManager->register('kissmetrics', 'CMService_KissMetrics_Client', array('km123'));

        $site = $this->getMockSite('CM_Site_Abstract', null, array('url' => 'http://www.example.com'));
        $viewer = $this->getMock('CM_Model_User', array('getIdRaw', 'getVisible'));
        $viewer->expects($this->any())->method('getIdRaw')->will($this->returnValue(array('id' => '1')));
        $viewer->expects($this->any())->method('getVisible')->will($this->returnValue(false));
        /** @var CM_Model_User $viewer */
        $render = new CM_Frontend_Render($site, $viewer);
        $layoutMock = $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $pageMock = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Mock' . uniqid());
        /** @var CM_Page_Abstract $pageMock */
        $renderAdapter = new CM_RenderAdapter_Layout($render, $pageMock);
        $html = new CMTest_TH_Html($renderAdapter->fetch());

        $this->assertContains('var _gaq = _gaq || [];', $html->getHtml());
        $this->assertContains("_gaq.push(['_setAccount', 'ga123']);", $html->getHtml());
        $this->assertContains("_gaq.push(['_setDomainName', 'www.example.com']);", $html->getHtml());
        $this->assertContains('var _kmq = _kmq || [];', $html->getHtml());
        $this->assertContains("var _kmk = _kmk || 'km123';", $html->getHtml());
        $this->assertContains("_kmq.push(['identify', 1]);", $html->getHtml());
    }
}
