<?php

class CM_Response_PageTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testProcessLanguageRedirect() {
        CMTest_TH::createLanguage('en');
        $user = CMTest_TH::createUser();
        $response = CMTest_TH::createResponsePage('/en/mock5', null, $user);
        $response->process();
        $this->assertContains('Location: ' . $response->getSite()->getUrl() . '/mock5', $response->getHeaders());
    }

    public function testProcessLanguageNoRedirect() {
        $language = CMTest_TH::createLanguage('en');
        $user = CMTest_TH::createUser();
        $response = CMTest_TH::createResponsePage('/en/mock5');
        $response->process();
        $this->assertEquals($language, $response->getRequest()->getLanguageUrl());

        $response = CMTest_TH::createResponsePage('/mock5');
        $response->process();
        $this->assertNull($response->getRequest()->getLanguageUrl());

        $response = CMTest_TH::createResponsePage('/mock5', null, $user);
        $response->process();
        $this->assertNull($response->getRequest()->getLanguageUrl());
        foreach ($response->getHeaders() as $header) {
            $this->assertNotContains('Location:', $header);
        }
    }

    public function testProcessHostRedirect() {
        $site = CM_Site_Abstract::factory();
        $redirectHeader = 'Location: http://' . $site->getHost() . '/mock5';

        $response = CMTest_TH::createResponsePage('/mock5', array('host' => $site->getHost()));
        $response->process();
        $this->assertNotContains($redirectHeader, $response->getHeaders());

        $response = CMTest_TH::createResponsePage('/mock5', array('host' => 'incorrect-host.org'));
        $response->process();
        $this->assertContains($redirectHeader, $response->getHeaders());
    }

    public function testProcessTrackingDisabled() {
        /** @var CM_Model_User $viewer */
        $response = CMTest_TH::createResponsePage('/mock5');
        $response->process();
        $html = $response->getContent();

        $this->assertNotContains("_gaq.push(['_trackPageview'", $html);
        $this->assertNotContains("_kmq.push(['identify'", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessTrackingGuest() {
        /** @var CM_Model_User $viewer */
        $response = CMTest_TH::createResponsePage('/mock5');
        $response->getRender()->setServiceManager($this->_getServiceManager('ga123', 'km123'));
        $response->process();
        $html = $response->getContent();

        $this->assertContains('var _gaq = _gaq || [];', $html);
        $this->assertContains("_gaq.push(['_setAccount', 'ga123']);", $html);
        $this->assertContains("_gaq.push(['_setDomainName', 'www.default.dev']);", $html);
        $this->assertContains("_gaq.push(['_trackPageview']);", $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $clientId = CM_Request_Abstract::getInstance()->getClientId();
        $this->assertContains("_kmq.push(['identify', 'c{$clientId}']);", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessTrackingViewer() {
        $viewer = $this->getMock('CM_Model_User', array('getIdRaw', 'getVisible', 'getLanguage'));
        $viewer->expects($this->any())->method('getIdRaw')->will($this->returnValue(array('id' => '1')));
        $viewer->expects($this->any())->method('getVisible')->will($this->returnValue(false));
        $viewer->expects($this->any())->method('getLanguage')->will($this->returnValue(null));
        /** @var CM_Model_User $viewer */
        $response = CMTest_TH::createResponsePage('/mock5', null, $viewer);
        $response->getRender()->setServiceManager($this->_getServiceManager('ga123', 'km123'));
        $response->process();
        $html = $response->getContent();

        $this->assertContains('var _gaq = _gaq || [];', $html);
        $this->assertContains("_gaq.push(['_setAccount', 'ga123']);", $html);
        $this->assertContains("_gaq.push(['_setDomainName', 'www.default.dev']);", $html);
        $this->assertContains("_gaq.push(['_trackPageview']);", $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $clientId = CM_Request_Abstract::getInstance()->getClientId();
        $this->assertContains("_kmq.push(['identify', 'c{$clientId}']);", $html);
        $this->assertContains("_kmq.push(['identify', 1]);", $html);
        $this->assertContains("_kmq.push(['alias', 'c{$clientId}', 1]);", $html);
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

class CM_Page_Mock5 extends CM_Page_Abstract {

    public function getLayout(CM_Frontend_Environment $environment, $layoutName = null) {
        return new CM_Layout_Mock();
    }
}

class CM_Layout_Mock extends CM_Layout_Abstract {

}
