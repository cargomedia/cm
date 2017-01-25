<?php

class CM_Http_Response_PageTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testProcessRedirect() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = new CM_Http_Request_Get('/mock11?count=3', ['host' => $site->getHost()]);
        $response = CM_Http_Response_Page::createFromRequest($request, $site, $this->getServiceManager());

        $response->process();
        $this->assertContains('Location: ' . $response->getSite()->getUrl() . '/mock11?count=2', $response->getHeaders());
    }

    public function testProcessLanguageNoRedirect() {
        $language = CMTest_TH::createLanguage('en');
        $site = (new CM_Site_SiteFactory())->getDefaultSite();

        $request = new CM_Http_Request_Get('/en/mock5', ['host' => $site->getHost()]);
        $response = CM_Http_Response_Page::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();
        $this->assertEquals($language, $response->getRequest()->getLanguageUrl());

        $request = new CM_Http_Request_Get('/mock5', ['host' => $site->getHost()]);
        $response = CM_Http_Response_Page::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();
        $this->assertNull($response->getRequest()->getLanguageUrl());

        $viewer = CMTest_TH::createUser();
        $request = new CM_Http_Request_Get('/mock5', ['host' => $site->getHost()], null, $viewer);
        $response = CM_Http_Response_Page::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();
        $this->assertNull($response->getRequest()->getLanguageUrl());
        foreach ($response->getHeaders() as $header) {
            $this->assertNotContains('Location:', $header);
        }
    }

    public function testProcessHostNoRedirect() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = new CM_Http_Request_Get('/mock5', ['host' => $site->getHost()]);
        $response = CM_Http_Response_Page::createFromRequest($request, $site, $this->getServiceManager());

        $response->process();
        $this->assertFalse(Functional\some($response->getHeaders(), function ($header) {
            return preg_match('/^Location:/', $header);
        }));
    }

    public function testProcessHostUnknownRedirect() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = new CM_Http_Request_Get('/mock5?foo=bar', ['host' => 'unknown-host.org']);
        $response = CM_Http_Response_Page::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();

        $this->assertContains('Location: ' . $site->getUrl() . '/mock5?foo=bar', $response->getHeaders());
    }

    public function testProcessHostWithoutWww() {
        $site = $this->getMockSite(null, null, ['url' => 'http://www.my-site.com']);
        $request = new CM_Http_Request_Get('/mock5?foo=bar', ['host' => 'my-site.com']);
        $response = CM_Http_Response_Page::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();

        $this->assertContains('Location: ' . $site->getUrl() . '/mock5?foo=bar', $response->getHeaders());
    }

    public function testProcessSiteMatchingByPath() {
        $site1 = $this->getMockSite(null, null, ['url' => 'http://my-site.com/foo']);
        $site2 = $this->getMockSite(null, null, ['url' => 'http://my-site.com/bar']);
        $site3 = $this->getMockSite(null, null, ['url' => 'http://my-site.com']);

        $expectedList = [
            '/foo/mock5?foo=bar' => $site1,
            '/bar/mock5?foo=bar' => $site2,
            '/mock5?foo=bar'     => $site3,
        ];

        $responseFactory = new CM_Http_ResponseFactory($this->getServiceManager());

        foreach ($expectedList as $path => $site) {
            $request = new CM_Http_Request_Get($path, ['host' => 'my-site.com']);
            $response = $responseFactory->getResponse($request);
            $response->process();

            $this->assertInstanceOf('CM_Http_Response_Page', $response);
            $this->assertEquals($site, $response->getSite());
        }
    }

    public function testProcessTrackingDisabled() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = new CM_Http_Request_Get('/mock5', ['host' => $site->getHost()]);
        $response = CM_Http_Response_Page::createFromRequest($request, $site, $this->getServiceManager());

        $response->process();
        $html = $response->getContent();
        $this->assertNotContains('ga("send", "pageview", "\/mock5")', $html);
        $this->assertNotContains("_kmq.push(['identify'", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessTrackingCanNotTrackPageView() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = new CM_Http_Request_Get('/mock8', ['host' => $site->getHost()]);
        $serviceManager = $this->_getServiceManager('ga123', 'km123');
        $response = CM_Http_Response_Page::createFromRequest($request, $site, $serviceManager);

        $response->process();
        $html = $response->getContent();
        $this->assertNotContains('ga("send", "pageview"', $html);
        $this->assertNotContains("_kmq.push(['identify'", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessTrackingVirtualPageView() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = new CM_Http_Request_Get('/mock9', ['host' => $site->getHost()]);
        $serviceManager = $this->_getServiceManager('ga123', 'km123');
        $response = CM_Http_Response_Page::createFromRequest($request, $site, $serviceManager);

        $response->process();
        $html = $response->getContent();
        $this->assertContains('ga("create", "ga123"', $html);
        $this->assertContains('ga("send", "pageview", "\/v\/foo")', $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $clientId = $request->getClientId();
        $this->assertContains("_kmq.push(['identify', 'Guest {$clientId}']);", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessTrackingVirtualPageViewWithError() {
        CM_Config::get()->CM_Http_Response_Page->exceptionsToCatch = [
            'CM_Exception_InvalidParam' => ['errorPage' => 'CM_Page_Error_NotFound', 'log' => false],
        ];
        $this->getMockClass('CM_Layout_Abstract', null, [], 'CM_Layout_Default');

        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = new CM_Http_Request_Get('/mock10', ['host' => $site->getHost()]);
        $serviceManager = $this->_getServiceManager('ga123', 'km123');
        $response = CM_Http_Response_Page::createFromRequest($request, $site, $serviceManager);

        $response->process();
        $html = $response->getContent();
        $this->assertContains('ga("create", "ga123"', $html);
        $this->assertContains('ga("send", "pageview", "\/v\/bar")', $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $clientId = $request->getClientId();
        $this->assertContains("_kmq.push(['identify', 'Guest {$clientId}']);", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessTrackingGuest() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = new CM_Http_Request_Get('/mock5', ['host' => $site->getHost()]);
        $serviceManager = $this->_getServiceManager('ga123', 'km123');
        $response = CM_Http_Response_Page::createFromRequest($request, $site, $serviceManager);

        $response->process();
        $html = $response->getContent();
        $this->assertContains('ga("create", "ga123"', $html);
        $this->assertContains('ga("send", "pageview", "\/mock5")', $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $clientId = $request->getClientId();
        $this->assertContains("_kmq.push(['identify', 'Guest {$clientId}']);", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testProcessTrackingViewer() {
        $mockBuilder = $this->getMockBuilder('CM_Model_User');
        $mockBuilder->setMethods(['getIdRaw', 'getVisible', 'getLanguage', 'getCurrency']);
        $viewerMock = $mockBuilder->getMock();
        $viewerMock->expects($this->any())->method('getIdRaw')->will($this->returnValue(array('id' => '1')));
        $viewerMock->expects($this->any())->method('getVisible')->will($this->returnValue(false));
        $viewerMock->expects($this->any())->method('getLanguage')->will($this->returnValue(null));
        $viewerMock->expects($this->any())->method('getCurrency')->will($this->returnValue(null));

        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = new CM_Http_Request_Get('/mock5', ['host' => $site->getHost()], null, $viewerMock);
        $serviceManager = $this->_getServiceManager('ga123', 'km123');
        $response = CM_Http_Response_Page::createFromRequest($request, $site, $serviceManager);

        $response->process();
        $html = $response->getContent();
        $this->assertContains('ga("create", "ga123"', $html);
        $this->assertContains('ga("send", "pageview", "\/mock5")', $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $clientId = $request->getClientId();
        $this->assertContains("_kmq.push(['identify', 'Guest {$clientId}']);", $html);
        $this->assertContains("_kmq.push(['identify', '1']);", $html);
        $this->assertContains("_kmq.push(['alias', 'Guest {$clientId}', '1']);", $html);
    }

    public function testProcessExceptionCatching() {
        CM_Config::get()->CM_Http_Response_Page->exceptionsToCatch = [
            'CM_Exception_InvalidParam' => ['errorPage' => 'CM_Page_Error_NotFound', 'log' => false],
        ];
        $this->getMockClass('CM_Layout_Abstract', null, [], 'CM_Layout_Default');

        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = new CM_Http_Request_Get('/example', ['host' => $site->getHost()]);
        /** @var CM_Http_Response_Page|\Mocka\AbstractClassTrait $response */
        $response = $this->mockObject('CM_Http_Response_Page', [$request, $site, $this->getServiceManager()]);
        $response->mockMethod('_renderPage')->set(function (CM_Page_Abstract $page) {
            if ($page instanceof CM_Page_Example) {
                throw new CM_Exception_InvalidParam();
            }
            return '<html>Error</html>';
        });

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

        $serviceManager->registerInstance('logger', new CM_Log_Logger(new CM_Log_Context()));
        $serviceManager->registerInstance('newrelic', new CMService_Newrelic(false, 'unit-test'));
        return $serviceManager;
    }
}

class CM_Page_Mock5 extends CM_Page_Abstract {

    public function getLayout(CM_Frontend_Environment $environment) {
        return CM_Layout_Mock::class;
    }
}

class CM_Page_Mock8 extends CM_Page_Abstract {

    public function getCanTrackPageView() {
        return false;
    }

    public function getLayout(CM_Frontend_Environment $environment) {
        return CM_Layout_Mock::class;
    }
}

class CM_Page_Mock9 extends CM_Page_Abstract {

    public function getPathVirtualPageView() {
        return '/v/foo';
    }

    public function getLayout(CM_Frontend_Environment $environment) {
        return CM_Layout_Mock::class;
    }
}

class CM_Page_Mock10 extends CM_Page_Abstract {

    public function getPathVirtualPageView() {
        return '/v/bar';
    }

    public function getLayout(CM_Frontend_Environment $environment) {
        return CM_Layout_Mock::class;
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

    public function getLayout(CM_Frontend_Environment $environment) {
        return CM_Layout_Mock::class;
    }
}

class CM_Layout_Mock extends CM_Layout_Abstract {

}
