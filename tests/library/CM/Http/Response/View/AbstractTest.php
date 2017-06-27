<?php

use CM\Url\Url;

class CM_Http_Response_View_AbstractTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testLoadPage() {
        $site = $this->getMockSite(null, ['url' => 'http://my-site.com'], ['name' => 'My site']);
        $page = new CM_Page_View_Ajax_Test_Mock();
        $env = new CM_Frontend_Environment($site, CMTest_TH::createUser());
        $params = [
            'path'          => $page::getPath(),
            'currentLayout' => $page->getLayout($env),
        ];
        $request = $this->createRequestAjax($page, 'loadPage', $params, null, null, $site);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);

        $this->assertViewResponseSuccess($response);
        $responseContent = CM_Params::decode($response->getContent(), true);
        $data = $responseContent['success']['data'];
        $pageRendering = $data['pageRendering'];
        $layoutRendering = $data['layoutRendering'];

        $this->assertNull($layoutRendering);
        $this->assertArrayHasKey('js', $pageRendering);
        $this->assertArrayHasKey('html', $pageRendering);
        $this->assertArrayHasKey('autoId', $pageRendering);
        $this->assertSame(array(), $data['menuEntryHashList']);
        $this->assertSame('My site', $data['title']);
        $this->assertSame($response->getRender()->getUrlPage('CM_Page_View_Ajax_Test_Mock'), $data['url']);
        $this->assertSame('', $data['jsTracking']);
    }

    public function testLoadPageDifferentLayout() {
        $site = $this->getMockSite(null, ['url' => 'http://my-site.com'], ['name' => 'My site']);
        $page = new CM_Page_View_Ajax_Test_Mock();
        $params = [
            'path'          => $page::getPath(),
            'currentLayout' => 'CM_Layout_Default',
        ];
        $request = $this->createRequestAjax($page, 'loadPage', $params, null, null, $site);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);

        $this->assertViewResponseSuccess($response);
        $responseContent = CM_Params::decode($response->getContent(), true);
        $data = $responseContent['success']['data'];
        $pageRendering = $data['pageRendering'];
        $layoutRendering = $data['layoutRendering'];

        $this->assertArrayHasKey('js', $layoutRendering);
        $this->assertArrayHasKey('html', $layoutRendering);
        $this->assertArrayHasKey('autoId', $layoutRendering);
        $this->assertArrayHasKey('js', $pageRendering);
        $this->assertArrayHasKey('html', $pageRendering);
        $this->assertArrayHasKey('autoId', $pageRendering);
        $this->assertSame(array(), $data['menuEntryHashList']);
        $this->assertSame('My site', $data['title']);
        $this->assertSame($response->getRender()->getUrlPage('CM_Page_View_Ajax_Test_Mock'), $data['url']);
        $this->assertSame('', $data['jsTracking']);
    }

    public function testLoadPageSiteWithPath() {
        $site = $this->getMockSite(null, ['url' => 'http://my-site.com/foo']);
        $page = new CM_Page_View_Ajax_Test_Mock();
        $env = new CM_Frontend_Environment($site, CMTest_TH::createUser());
        $params = [
            'path'          => '/foo' . $page::getPath(),
            'currentLayout' => $page->getLayout($env),
        ];
        $request = $this->createRequestAjax($page, 'loadPage', $params, null, null, $site);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);

        $this->assertViewResponseSuccess($response);
        $responseContent = CM_Params::decode($response->getContent(), true);
        $this->assertSame($response->getRender()->getUrlPage('CM_Page_View_Ajax_Test_Mock'), $responseContent['success']['data']['url']);
    }

    public function testProcessExceptionCatching() {
        CM_Config::get()->CM_Http_Response_View_Abstract->catchPublicExceptions = true;
        CM_Config::get()->CM_Http_Response_View_Abstract->exceptionsToCatch = ['CM_Exception_Nonexistent' => []];
        /** @var CM_Http_Response_View_Abstract|\Mocka\AbstractClassTrait $response */
        $response = $this->mockClass('CM_Http_Response_View_Abstract')->newInstanceWithoutConstructor();
        $response->mockMethod('_processView')->set(function () {
            throw new CM_Exception_Invalid('foo', null, null, [
                'messagePublic' => new CM_I18n_Phrase('bar'),
            ]);
        });
        $response->mockMethod('getRender')->set(new CM_Frontend_Render());
        CMTest_TH::callProtectedMethod($response, '_process');
        $this->assertViewResponseError($response, 'CM_Exception_Invalid', 'bar', true);

        $response->mockMethod('_processView')->set(function () {
            throw new CM_Exception_Nonexistent('foo');
        });

        CMTest_TH::callProtectedMethod($response, '_process');
        $this->assertViewResponseError($response, 'CM_Exception_Nonexistent', 'Internal server error', false);
    }

    public function testProcessReturnDeployVersion() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $page = new CM_Page_View_Ajax_Test_Mock();
        $env = new CM_Frontend_Environment($site, CMTest_TH::createUser());
        $params = [
            'path'          => CM_Page_View_Ajax_Test_MockRedirect::getPath(),
            'currentLayout' => $page->getLayout($env),
        ];
        $response = $this->getResponseAjax($page, 'loadPage', $params);
        $responseDecoded = CM_Params::jsonDecode($response->getContent());
        $this->assertArrayHasKey('deployVersion', $responseDecoded);
        $this->assertSame(CM_App::getInstance()->getDeployVersion(), $responseDecoded['deployVersion']);
    }

    public function testLoadPageRedirectDifferentHost() {
        $site = $this->getMockSite(null, ['url' => 'http://my-site.com/foo']);
        $page = new CM_Page_View_Ajax_Test_Mock();
        $env = new CM_Frontend_Environment($site, CMTest_TH::createUser());
        $params = [
            'path'          => CM_Page_View_Ajax_Test_MockRedirect::getPath(),
            'currentLayout' => $page->getLayout($env),
        ];
        $response = $this->getResponseAjax($page, 'loadPage', $params);
        $this->assertViewResponseSuccess($response, array('redirectExternal' => 'http://www.foo.bar'));
    }

    public function testLoadPageRedirectDifferentSitePath() {
        $site1 = $this->getMockSite(null, ['url' => 'http://my-site.com/foo']);
        $site2 = $this->getMockSite(null, ['url' => 'http://my-site.com/bar']);
        $page = new CM_Page_View_Ajax_Test_Mock();
        $env = new CM_Frontend_Environment($site1, CMTest_TH::createUser());
        $params = [
            'path'          => '/bar' . $page::getPath(),
            'currentLayout' => $page->getLayout($env),
        ];
        $request = $this->createRequestAjax($page, 'loadPage', $params, null, null, $site1);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);

        $this->assertInstanceOf('CM_Http_Response_View_Abstract', $response);
        $this->assertViewResponseSuccess($response);
        $responseDecoded = CM_Params::jsonDecode($response->getContent());
        $url = Url::create(CM_Page_View_Ajax_Test_Mock::getPath())->withBaseUrl($site2->getUrl());
        $this->assertSame((string) $url, $responseDecoded['success']['data']['redirectExternal']);
    }

    public function testLoadPageRedirectDifferentSitePathRemove() {
        $site1 = $this->getMockSite(null, ['url' => 'http://my-site.com/foo']);
        $site2 = $this->getMockSite(null, ['url' => 'http://my-site.com']);

        $page = new CM_Page_View_Ajax_Test_Mock();
        $env = new CM_Frontend_Environment($site1, CMTest_TH::createUser());
        $params = [
            'path'          => $page::getPath(),
            'currentLayout' => $page->getLayout($env),
        ];
        $request = $this->createRequestAjax($page, 'loadPage', $params, null, null, $site1);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);

        $this->assertInstanceOf('CM_Http_Response_View_Abstract', $response);
        $this->assertViewResponseSuccess($response);
        $responseDecoded = CM_Params::jsonDecode($response->getContent());
        $url = Url::create(CM_Page_View_Ajax_Test_Mock::getPath())->withBaseUrl($site2->getUrl());
        $this->assertSame((string) $url, $responseDecoded['success']['data']['redirectExternal']);
    }

    public function testLoadPageRedirectDifferentSitePathAdd() {
        $site1 = $this->getMockSite(null, ['url' => 'http://my-site.com/foo']);
        $site2 = $this->getMockSite(null, ['url' => 'http://my-site.com']);

        $page = new CM_Page_View_Ajax_Test_Mock();
        $env = new CM_Frontend_Environment($site2, CMTest_TH::createUser());
        $params = [
            'path'          => '/foo' . $page::getPath(),
            'currentLayout' => $page->getLayout($env),
        ];
        $request = $this->createRequestAjax($page, 'loadPage', $params, null, null, $site2);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);

        $this->assertInstanceOf('CM_Http_Response_View_Abstract', $response);
        $this->assertViewResponseSuccess($response);
        $responseDecoded = CM_Params::jsonDecode($response->getContent());
        $url = Url::create(CM_Page_View_Ajax_Test_Mock::getPath())->withBaseUrl($site1->getUrl());
        $this->assertSame((string) $url, $responseDecoded['success']['data']['redirectExternal']);
    }

    public function testLoadPageNotRedirectLanguage() {
        $site = $this->getMockSite(null, ['url' => 'http://my-site.com']);
        CMTest_TH::createLanguage('en');
        $viewer = CMTest_TH::createUser();
        $page = new CM_Page_View_Ajax_Test_Mock();
        $env = new CM_Frontend_Environment($site, $viewer);
        $params = [
            'path'          => '/en' . $page::getPath(),
            'currentLayout' => $page->getLayout($env),
        ];
        $request = $this->createRequestAjax($page, 'loadPage', $params, null, null, $site);
        $request->mockMethod('getViewer')->set($viewer);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);

        $this->assertViewResponseSuccess($response);
        $responseDecoded = CM_Params::jsonDecode($response->getContent());
        $url = Url::create('/en' . CM_Page_View_Ajax_Test_Mock::getPath())->withBaseUrl($site->getUrl());
        $this->assertSame((string) $url, $responseDecoded['success']['data']['url']);
    }

    public function testLoadPageTracking() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $page = new CM_Page_View_Ajax_Test_Mock();
        $env = new CM_Frontend_Environment($site, CMTest_TH::createUser());
        $params = [
            'path'          => $page::getPath(),
            'currentLayout' => $page->getLayout($env),
        ];
        $request = $this->createRequestAjax($page, 'loadPage', $params);
        $response = CM_Http_Response_View_Ajax::createFromRequest($request, $site, $this->_getServiceManagerWithGA('ga123'));
        $response->process();

        $this->assertViewResponseSuccess($response);
        $responseContent = CM_Params::decode($response->getContent(), true);
        $pageview = CM_Params::jsonEncode($page::getPath());
        $this->assertContains('ga("send", "pageview", ' . $pageview . ')', $responseContent['success']['data']['jsTracking']);
    }

    public function testLoadPageTrackingRedirect() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $page = new CM_Page_View_Ajax_Test_MockRedirectSelf();
        $env = new CM_Frontend_Environment($site, CMTest_TH::createUser());
        $params = [
            'path'          => $page::getPath() . '?count=3',
            'currentLayout' => $page->getLayout($env),
        ];
        $request = $this->createRequestAjax($page, 'loadPage', $params);
        $response = CM_Http_Response_View_Ajax::createFromRequest($request, $site, $this->_getServiceManagerWithGA('ga123'));
        $response->process();

        $this->assertViewResponseSuccess($response);
        $responseContent = CM_Params::decode($response->getContent(), true);
        $jsTracking = $responseContent['success']['data']['jsTracking'];
        $this->assertSame(1, substr_count($jsTracking, 'ga("send", "pageview"'));
        $pageview = CM_Params::jsonEncode($page::getPath() . '?count=0');
        $this->assertContains('ga("send", "pageview", ' . $pageview . ')', $jsTracking);
    }

    public function testLoadPageTrackingError() {
        CM_Config::get()->CM_Http_Response_Page->exceptionsToCatch['CM_Exception_Nonexistent'] = [
            'errorPage' => 'CM_Page_View_Ajax_Test_Mock',
        ];

        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $page = new CM_Page_View_Ajax_Test_Mock();
        $env = new CM_Frontend_Environment($site, CMTest_TH::createUser());
        $params = [
            'path'          => '/iwhdfkjlsh',
            'currentLayout' => $page->getLayout($env),
        ];
        $request = $this->createRequestAjax($page, 'loadPage', $params);
        $response = CM_Http_Response_View_Ajax::createFromRequest($request, $site, $this->_getServiceManagerWithGA('ga123'));
        $response->process();

        $this->assertViewResponseSuccess($response);
        $responseContent = CM_Params::decode($response->getContent(), true);
        $jsTracking = $responseContent['success']['data']['jsTracking'];
        $html = $responseContent['success']['data']['pageRendering']['html'];

        $this->assertSame(1, substr_count($jsTracking, 'ga("send", "pageview"'));
        $this->assertContains('ga("send", "pageview", "\/iwhdfkjlsh")', $jsTracking);
        $this->assertContains('CM_Page_View_Ajax_Test_Mock', $html);
    }

    public function testReloadComponent() {
        $component = new CM_Component_Notfound([]);
        $viewResponse = new CM_Frontend_ViewResponse($component);
        $request = $this->createRequestAjax($component, 'reloadComponent', ['foo' => 'bar'], $viewResponse);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);
        $this->assertViewResponseSuccess($response);

        $jsonResponse = CM_Util::jsonDecode($response->getContent());

        // no better way to get the autoId because Response::_getComponentRendering() creates a new Render instance
        $autoId = $jsonResponse['success']['data']['autoId'];
        $js = sprintf('cm.views["%s"] = new CM_Component_Notfound({el:$("#%s").get(0),params:{"foo":"bar"}});', $autoId, $autoId);
        $html = sprintf("<div id=\"%s\" class=\"CM_Component_Notfound CM_Component_Abstract CM_View_Abstract\">Sorry, this page was not found. It has been removed or never existed.\n</div>", $autoId);
        $this->assertSame($js, $jsonResponse['success']['data']['js']);
        $this->assertSame($html, $jsonResponse['success']['data']['html']);
    }

    public function testReloadComponentAdditionalParams() {
        $entity = CM_Model_Entity_Mock2::createStatic();
        $config = CM_Config::get();
        $config->CM_Model_Abstract->types[CM_Model_Entity_Mock2::getTypeStatic()] = get_class($entity);
        $config->CM_Model_Entity_Mock2 = new stdClass();
        $config->CM_Model_Entity_Mock2->type = CM_Model_Entity_Mock2::getTypeStatic();

        $component = new CM_Component_Mock(['entity' => $entity, 'foo' => 'bar', 'foz' => 'baz']);
        $viewResponse = new CM_Frontend_ViewResponse($component);
        $request = $this->createRequestAjax($component, 'reloadComponent', ['foz' => 'toto'], $viewResponse);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);
        $this->assertViewResponseSuccess($response);

        $jsonResponse = CM_Util::jsonDecode($response->getContent());

        // no better way to get the autoId because Response::_getComponentRendering() creates a new Render instance
        $autoId = $jsonResponse['success']['data']['autoId'];
        $js = sprintf('cm.views["%s"] = new CM_Component_Mock({el:$("#%s").get(0),params:{"entity":{"_class":"CM_Model_Entity_Mock2","_type":123,"_id":{"id":"1"},"id":1,"path":null},"foo":"bar","foz":"toto"}});', $autoId, $autoId);
        $html = sprintf("<div id=\"%s\" class=\"CM_Component_Mock CM_Component_Notfound CM_Component_Abstract CM_View_Abstract\">Sorry, this page was not found. It has been removed or never existed.\n</div>", $autoId);
        $this->assertSame($js, $jsonResponse['success']['data']['js']);
        $this->assertSame($html, $jsonResponse['success']['data']['html']);
    }

    public function testLoadComponent() {
        $response = $this->getResponseAjax(new CM_Component_Graph(), 'loadComponent', ['className' => 'CM_Component_Graph', 'series' => []]);
        $this->assertViewResponseSuccess($response);
        $successContent = CM_Params::decode($response->getContent(), true)['success'];

        $autoId = $successContent['data']['autoId'];
        $this->assertNotEmpty($autoId);
        $html = new CM_Dom_NodeList($successContent['data']['html']);
        $this->assertSame($autoId, $html->getAttribute('id'));
        $this->assertArrayNotHasKey('exec', $successContent);
        $this->assertContains('new CM_Component_Graph', $successContent['data']['js']);
    }

    /**
     * @param string $code
     * @return CM_Service_Manager
     */
    private function _getServiceManagerWithGA($code) {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->registerInstance('googleanalytics', new CMService_GoogleAnalytics_Client($code));
        $serviceManager->registerInstance('trackings', new CM_Service_Trackings(['googleanalytics']));
        $serviceManager->registerInstance('logger', $this->getServiceManager()->getLogger());
        $serviceManager->registerInstance('newrelic', $this->getServiceManager()->getNewrelic());

        return $serviceManager;
    }
}

class CM_Page_View_Ajax_Test_MockRedirect extends CM_Page_Abstract {

    public function prepareResponse(CM_Frontend_Environment $environment, CM_Http_Response_Page $response) {
        $response->redirectUrl('http://www.foo.bar');
    }
}

class CM_Page_View_Ajax_Test_Mock extends CM_Page_Abstract {

    public function getLayout(CM_Frontend_Environment $environment) {
        return CM_Layout_Mock1::class;
    }
}

class CM_Page_View_Ajax_Test_MockRedirectSelf extends CM_Page_Abstract {

    public function prepareResponse(CM_Frontend_Environment $environment, CM_Http_Response_Page $response) {
        $count = $this->_params->getInt('count');
        if ($count > 0) {
            $response->redirect($this, ['count' => --$count]);
        }
    }

    public function getLayout(CM_Frontend_Environment $environment) {
        return CM_Layout_Mock1::class;
    }
}

class CM_Layout_Mock1 extends CM_Layout_Abstract {

}

class CM_Component_Mock extends CM_Component_Notfound {

    public function prepare(CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        if ($this->_params->has('entity')) {
            $entity = $this->_params->getEntity('entity');
            $viewResponse->set('entity', $entity);
        }
    }

    public function checkAccessible(CM_Frontend_Environment $environment) {
    }
}

class CM_Model_Entity_Mock2 extends CM_Model_Entity_Abstract {

    public function getPath() {
        return null;
    }

    protected function _loadData() {
        return array();
    }

    protected static function _createStatic(array $data) {
        return new self(1);
    }

    public static function getTypeStatic() {
        return 123;
    }
}
