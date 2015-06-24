<?php

class CM_Http_Response_PageTest extends CMTest_TestCase {

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

    public function testProcessLanguageRedirect_parameter() {
        CMTest_TH::createLanguage('en');
        $user = CMTest_TH::createUser();
        $location = CMTest_TH::createLocation();
        $locationEncoded = CM_Params::encode($location, true);
        $query = http_build_query(['location' => $locationEncoded]);
        $response = CMTest_TH::createResponsePage('/en/mock5?' . $query, null, $user);
        $response->process();
        $siteUrl = $response->getSite()->getUrl();
        $this->assertContains('Location: ' . $siteUrl . '/mock5?' . $query, $response->getHeaders());
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

        $response = CMTest_TH::createResponsePage('/mock5', array('host' => $site->getHost()));
        $response->process();
        $this->assertEmpty($response->getHeaders());

        $response = CMTest_TH::createResponsePage('/mock5', array('host' => 'incorrect-host.org'));
        $response->process();
        $siteUrl = 'http://' . $site->getHost();
        $this->assertContains('Location: ' . $siteUrl . '/mock5', $response->getHeaders());
    }

    public function testProcessHostRedirect_parameter() {
        $site = CM_Site_Abstract::factory();

        $location = CMTest_TH::createLocation();
        $locationEncoded = CM_Params::encode($location, true);
        $query = http_build_query(['location' => $locationEncoded]);
        $response = CMTest_TH::createResponsePage('/mock5?' . $query, array('host' => 'incorrect-host.org'));
        $response->process();
        $siteUrl = 'http://' . $site->getHost();
        $this->assertContains('Location: ' . $siteUrl . '/mock5?' . $query, $response->getHeaders());
    }

    public function testProcessTrackingDisabled() {
        /** @var CM_Model_User $viewer */
        $response = CMTest_TH::createResponsePage('/mock5');
        $response->process();
        $html = $response->getContent();

        $this->assertNotContains('ga("send", "pageview", "/mock5")', $html);
        $this->assertNotContains("_kmq.push(['identify'", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessCanNotTrackPageView() {
        /** @var CM_Model_User $viewer */
        $response = CMTest_TH::createResponsePage('/mock8');
        $response->getRender()->setServiceManager($this->_getServiceManager('ga123', 'km123'));
        $response->process();
        $html = $response->getContent();

        $this->assertNotContains('ga("send", "pageview"', $html);
        $this->assertNotContains("_kmq.push(['identify'", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessTrackingGuest() {
        /** @var CM_Model_User $viewer */
        $response = CMTest_TH::createResponsePage('/mock5');
        $response->getRender()->setServiceManager($this->_getServiceManager('ga123', 'km123'));
        $response->process();
        $html = $response->getContent();

        $this->assertContains('ga("create", "ga123"', $html);
        $this->assertContains('ga("send", "pageview", "/mock5")', $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $clientId = CM_Http_Request_Abstract::getInstance()->getClientId();
        $this->assertContains("_kmq.push(['identify', 'Guest {$clientId}']);", $html);
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

        $this->assertContains('ga("create", "ga123"', $html);
        $this->assertContains('ga("send", "pageview", "/mock5")', $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $clientId = CM_Http_Request_Abstract::getInstance()->getClientId();
        $this->assertContains("_kmq.push(['identify', 'Guest {$clientId}']);", $html);
        $this->assertContains("_kmq.push(['identify', '1']);", $html);
        $this->assertContains("_kmq.push(['alias', 'Guest {$clientId}', '1']);", $html);
    }

    public function testProcessExceptionCatching() {
        CM_Config::get()->CM_Http_Response_Page->exceptionsToCatch = [
            'CM_Exception_InvalidParam' => ['errorPage' => 'CM_Page_Error_NotFound', 'log' => null],
        ];
        $this->getMock('CM_Layout_Abstract', null, [], 'CM_Layout_Default');
        $request = CMTest_TH::createResponsePage('/example')->getRequest();
        $response = $this->mockObject('CM_Http_Response_Page', [$request, CMTest_TH::getServiceManager()]);
        $response->mockMethod('_renderPage')->set(function () {
            static $counter = 0;
            if ($counter++ === 0) { // don't throw when rendering the error-page the request was redirected to
                throw new CM_Exception_InvalidParam();
            }
        });
        /** @var CM_Http_Response_Page $response */

        $this->assertSame('/example', $response->getRequest()->getPath());
        $response->process();
        $this->assertSame('/error/not-found', $response->getRequest()->getPath());
    }

    /**
     * @param string $codeGoogleAnalytics
     * @param string $codeKissMetrics
     * @return CM_Service_Manager
     */
    protected function _getServiceManager($codeGoogleAnalytics, $codeKissMetrics) {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->registerInstance('googleanalytics', new CMService_GoogleAnalytics_Client($codeGoogleAnalytics));
        $serviceManager->registerInstance('kissmetrics', new CMService_KissMetrics_Client($codeKissMetrics));
        $serviceManager->registerInstance('trackings', new CM_Service_Trackings(['googleanalytics', 'kissmetrics']));
        return $serviceManager;
    }
}

class CM_Page_Mock5 extends CM_Page_Abstract {

    public function getLayout(CM_Frontend_Environment $environment, $layoutName = null) {
        return new CM_Layout_Mock();
    }
}

class CM_Page_Mock8 extends CM_Page_Abstract {

    public function getLayout(CM_Frontend_Environment $environment, $layoutName = null) {
        return new CM_Layout_Mock();
    }

    public function getCanTrackPageView() {
        return false;
    }
}

class CM_Layout_Mock extends CM_Layout_Abstract {

}
