<?php

class CM_Http_Response_PageTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testProcessRedirect() {
        $response = CMTest_TH::createResponsePage('/mock11?count=3');
        $response->process();
        $this->assertContains('Location: ' . $response->getSite()->getUrl() . '/mock11?count=2', $response->getHeaders());
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
        $this->assertFalse(Functional\some($response->getHeaders(), function($header) {
            return preg_match('/^Location:/', $header);
        }));

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
        $response = CMTest_TH::createResponsePage('/mock5');
        $response->process();
        $html = $response->getContent();

        $this->assertNotContains('ga("send", "pageview", "\/mock5")', $html);
        $this->assertNotContains("_kmq.push(['identify'", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessTrackingCanNotTrackPageView() {
        $response = CMTest_TH::createResponsePage('/mock8');
        $response->setServiceManager($this->_getServiceManager('ga123', 'km123'));
        $this->callProtectedMethod($response, '_process');
        $html = $response->getContent();

        $this->assertNotContains('ga("send", "pageview"', $html);
        $this->assertNotContains("_kmq.push(['identify'", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessTrackingVirtualPageView() {
        $response = CMTest_TH::createResponsePage('/mock9');
        $response->setServiceManager($this->_getServiceManager('ga123', 'km123'));
        $this->callProtectedMethod($response, '_process');
        $html = $response->getContent();

        $this->assertContains('ga("create", "ga123"', $html);
        $this->assertContains('ga("send", "pageview", "\/v\/foo")', $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $clientId = CM_Http_Request_Abstract::getInstance()->getClientId();
        $this->assertContains("_kmq.push(['identify', 'Guest {$clientId}']);", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessTrackingVirtualPageViewWithError() {
        CM_Config::get()->CM_Http_Response_Page->exceptionsToCatch = [
            'CM_Exception_InvalidParam' => ['errorPage' => 'CM_Page_Error_NotFound', 'log' => false],
        ];
        $this->getMock('CM_Layout_Abstract', null, [], 'CM_Layout_Default');
        $response = CMTest_TH::createResponsePage('/mock10');
        $response->setServiceManager($this->_getServiceManager('ga123', 'km123'));
        $this->callProtectedMethod($response, '_process');
        $html = $response->getContent();

        $this->assertContains('ga("create", "ga123"', $html);
        $this->assertContains('ga("send", "pageview", "\/v\/bar")', $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $clientId = CM_Http_Request_Abstract::getInstance()->getClientId();
        $this->assertContains("_kmq.push(['identify', 'Guest {$clientId}']);", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessTrackingGuest() {
        $response = CMTest_TH::createResponsePage('/mock5');
        $response->setServiceManager($this->_getServiceManager('ga123', 'km123'));
        $this->callProtectedMethod($response, '_process');
        $html = $response->getContent();

        $this->assertContains('ga("create", "ga123"', $html);
        $this->assertContains('ga("send", "pageview", "\/mock5")', $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $clientId = CM_Http_Request_Abstract::getInstance()->getClientId();
        $this->assertContains("_kmq.push(['identify', 'Guest {$clientId}']);", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessTrackingViewer() {
        $viewer = $this->getMock('CM_Model_User', array('getIdRaw', 'getVisible', 'getLanguage', 'getCurrency'));
        $viewer->expects($this->any())->method('getIdRaw')->will($this->returnValue(array('id' => '1')));
        $viewer->expects($this->any())->method('getVisible')->will($this->returnValue(false));
        $viewer->expects($this->any())->method('getLanguage')->will($this->returnValue(null));
        $viewer->expects($this->any())->method('getCurrency')->will($this->returnValue(null));
        /** @var CM_Model_User $viewer */
        $response = CMTest_TH::createResponsePage('/mock5', null, $viewer);
        $response->setServiceManager($this->_getServiceManager('ga123', 'km123'));
        $this->callProtectedMethod($response, '_process');
        $html = $response->getContent();

        $this->assertContains('ga("create", "ga123"', $html);
        $this->assertContains('ga("send", "pageview", "\/mock5")', $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $clientId = CM_Http_Request_Abstract::getInstance()->getClientId();
        $this->assertContains("_kmq.push(['identify', 'Guest {$clientId}']);", $html);
        $this->assertContains("_kmq.push(['identify', '1']);", $html);
        $this->assertContains("_kmq.push(['alias', 'Guest {$clientId}', '1']);", $html);
    }

    public function testProcessExceptionCatching() {
        CM_Config::get()->CM_Http_Response_Page->exceptionsToCatch = [
            'CM_Exception_InvalidParam' => ['errorPage' => 'CM_Page_Error_NotFound', 'log' => false],
        ];
        $this->getMock('CM_Layout_Abstract', null, [], 'CM_Layout_Default');
        $request = CMTest_TH::createResponsePage('/example')->getRequest();

        /** @var CM_Http_Response_Page|\Mocka\AbstractClassTrait $response */
        $response = $this->mockObject('CM_Http_Response_Page', [$request, CMTest_TH::getServiceManager()]);
        $response->mockMethod('_renderPage')->set(function (CM_Page_Abstract $page) {
            if ($page instanceof CM_Page_Example) {
                throw new CM_Exception_InvalidParam();
            }
            return '<html>Error</html>';
        });

        $this->assertSame('/example', $response->getRequest()->getPath());
        $this->callProtectedMethod($response, '_process');
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

    public function getCanTrackPageView() {
        return false;
    }

    public function getLayout(CM_Frontend_Environment $environment, $layoutName = null) {
        return new CM_Layout_Mock();
    }
}

class CM_Page_Mock9 extends CM_Page_Abstract {

    public function getPathVirtualPageView() {
        return '/v/foo';
    }

    public function getLayout(CM_Frontend_Environment $environment, $layoutName = null) {
        return new CM_Layout_Mock();
    }
}

class CM_Page_Mock10 extends CM_Page_Abstract {

    public function getPathVirtualPageView() {
        return '/v/bar';
    }

    public function getLayout(CM_Frontend_Environment $environment, $layoutName = null) {
        return new CM_Layout_Mock();
    }

    public function prepare(CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        throw new CM_Exception_InvalidParam();
    }
}

class CM_Page_Mock11 extends CM_Page_Abstract {

    public function prepareResponse(CM_Frontend_Environment $environment, CM_Http_Response_Page $response) {
        $count = $this->_params->getInt('count');
        if ($count > 0) {
            $response->redirect($this, ['count' => --$count]);
        }
    }

    public function getLayout(CM_Frontend_Environment $environment, $layoutName = null) {
        return new CM_Layout_Mock();
    }
}

class CM_Layout_Mock extends CM_Layout_Abstract {

}
