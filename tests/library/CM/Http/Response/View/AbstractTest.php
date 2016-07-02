<?php

class CM_Http_Response_View_AbstractTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testLoadPage() {
        $site = $this->getMockSite(null, null, ['url' => 'http://my-site.com']);
        $page = new CM_Page_View_Ajax_Test_Mock();
        $this->getMock('CM_Layout_Abstract', null, [], 'CM_Layout_Default');
        $request = $this->createRequestAjax($page, 'loadPage', ['path' => CM_Page_View_Ajax_Test_Mock::getPath()], null, null, $site);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);

        $this->assertViewResponseSuccess($response);
        $responseContent = CM_Params::decode($response->getContent(), true);
        $this->assertArrayHasKey('js', $responseContent['success']['data']);
        $this->assertArrayHasKey('html', $responseContent['success']['data']);
        $this->assertArrayHasKey('autoId', $responseContent['success']['data']);
        $this->assertSame(array(), $responseContent['success']['data']['menuEntryHashList']);
        $this->assertSame('', $responseContent['success']['data']['title']);
        $this->assertSame($response->getRender()->getUrlPage('CM_Page_View_Ajax_Test_Mock'), $responseContent['success']['data']['url']);
        $this->assertSame('CM_Layout_Mock1', $responseContent['success']['data']['layoutClass']);
    }

    public function testLoadPageSiteWithPath() {
        $site = $this->getMockSite(null, null, ['url' => 'http://my-site.com/foo']);
        $page = new CM_Page_View_Ajax_Test_Mock();
        $this->getMock('CM_Layout_Abstract', null, [], 'CM_Layout_Default');
        $request = $this->createRequestAjax($page, 'loadPage', ['path' => '/foo' . CM_Page_View_Ajax_Test_Mock::getPath()], null, null, $site);
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
        $response = $this->getResponseAjax(new CM_Page_View_Ajax_Test_Mock(), 'loadPage', ['path' => CM_Page_View_Ajax_Test_MockRedirect::getPath()]);
        $responseDecoded = CM_Params::jsonDecode($response->getContent());
        $this->assertArrayHasKey('deployVersion', $responseDecoded);
        $this->assertSame(CM_App::getInstance()->getDeployVersion(), $responseDecoded['deployVersion']);
    }

    public function testLoadPageRedirectDifferntHost() {
        $response = $this->getResponseAjax(new CM_Page_View_Ajax_Test_Mock(), 'loadPage', ['path' => CM_Page_View_Ajax_Test_MockRedirect::getPath()]);
        $this->assertViewResponseSuccess($response, array('redirectExternal' => 'http://www.foo.bar'));
    }

    public function testLoadPageRedirectDifferentSitePath() {
        $site1 = $this->getMockSite(null, null, ['url' => 'http://my-site.com/foo']);
        $site2 = $this->getMockSite(null, null, ['url' => 'http://my-site.com/bar']);

        $view = new CM_Page_View_Ajax_Test_Mock();
        $request = $this->createRequestAjax($view, 'loadPage', ['path' => '/bar' . CM_Page_View_Ajax_Test_Mock::getPath()], null, null, $site1);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);

        $this->assertInstanceOf('CM_Http_Response_View_Abstract', $response);
        $this->assertViewResponseSuccess($response);
        $responseDecoded = CM_Params::jsonDecode($response->getContent());
        $this->assertSame($site2->getUrl() . CM_Page_View_Ajax_Test_Mock::getPath(), $responseDecoded['success']['data']['redirectExternal']);
    }

    public function testLoadPageRedirectDifferentSitePathRemove() {
        $site1 = $this->getMockSite(null, null, ['url' => 'http://my-site.com/foo']);
        $site2 = $this->getMockSite(null, null, ['url' => 'http://my-site.com']);

        $view = new CM_Page_View_Ajax_Test_Mock();
        $request = $this->createRequestAjax($view, 'loadPage', ['path' => CM_Page_View_Ajax_Test_Mock::getPath()], null, null, $site1);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);

        $this->assertInstanceOf('CM_Http_Response_View_Abstract', $response);
        $this->assertViewResponseSuccess($response);
        $responseDecoded = CM_Params::jsonDecode($response->getContent());
        $this->assertSame($site2->getUrl() . CM_Page_View_Ajax_Test_Mock::getPath(), $responseDecoded['success']['data']['redirectExternal']);
    }

    public function testLoadPageRedirectDifferentSitePathAdd() {
        $site1 = $this->getMockSite(null, null, ['url' => 'http://my-site.com/foo']);
        $site2 = $this->getMockSite(null, null, ['url' => 'http://my-site.com']);

        $view = new CM_Page_View_Ajax_Test_Mock();
        $request = $this->createRequestAjax($view, 'loadPage', ['path' => '/foo' . CM_Page_View_Ajax_Test_Mock::getPath()], null, null, $site2);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);

        $this->assertInstanceOf('CM_Http_Response_View_Abstract', $response);
        $this->assertViewResponseSuccess($response);
        $responseDecoded = CM_Params::jsonDecode($response->getContent());
        $this->assertSame($site1->getUrl() . CM_Page_View_Ajax_Test_Mock::getPath(), $responseDecoded['success']['data']['redirectExternal']);
    }

    public function testLoadPageRedirectLanguage() {
        $site = $this->getMockSite(null, null, ['url' => 'http://my-site.com']);
        CMTest_TH::createLanguage('en');
        $viewer = CMTest_TH::createUser();
        $view = new CM_Page_View_Ajax_Test_Mock();
        $request = $this->createRequestAjax($view, 'loadPage', ['path' => '/en' . CM_Page_View_Ajax_Test_Mock::getPath()], null, null, $site);
        $request->mockMethod('getViewer')->set($viewer);
        /** @var CM_Http_Response_View_Abstract $response */
        $response = $this->processRequest($request);

        $this->assertViewResponseSuccess($response);
        $responseDecoded = CM_Params::jsonDecode($response->getContent());
        $this->assertSame($site->getUrl() . CM_Page_View_Ajax_Test_Mock::getPath(), $responseDecoded['success']['data']['url']);
    }

    public function testLoadPageTracking() {
        $site = CM_Site_Abstract::factory();
        $page = new CM_Page_View_Ajax_Test_Mock();
        $request = $this->createRequestAjax($page, 'loadPage', ['path' => $page::getPath()]);
        $response = CM_Http_Response_View_Ajax::createFromRequest($request, $site, $this->_getServiceManagerWithGA('ga123'));
        $response->process();

        $this->assertViewResponseSuccess($response);
        $responseContent = CM_Params::decode($response->getContent(), true);
        $pageview = CM_Params::jsonEncode($page::getPath());
        $this->assertContains('ga("send", "pageview", ' . $pageview . ')', $responseContent['success']['data']['jsTracking']);
    }

    public function testLoadPageTrackingRedirect() {
        $site = CM_Site_Abstract::factory();
        $page = new CM_Page_View_Ajax_Test_MockRedirectSelf();
        $request = $this->createRequestAjax($page, 'loadPage', ['path' => $page::getPath() . '?count=3']);
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

        $site = CM_Site_Abstract::factory();
        $page = new CM_Page_View_Ajax_Test_Mock();
        $request = $this->createRequestAjax($page, 'loadPage', ['path' => '/iwhdfkjlsh']);
        $response = CM_Http_Response_View_Ajax::createFromRequest($request, $site, $this->_getServiceManagerWithGA('ga123'));
        $response->process();

        $this->assertViewResponseSuccess($response);
        $responseContent = CM_Params::decode($response->getContent(), true);
        $jsTracking = $responseContent['success']['data']['jsTracking'];
        $html = $responseContent['success']['data']['html'];

        $this->assertSame(1, substr_count($jsTracking, 'ga("send", "pageview"'));
        $this->assertContains('ga("send", "pageview", "\/iwhdfkjlsh")', $jsTracking);
        $this->assertContains('CM_Page_View_Ajax_Test_Mock', $html);
    }

    public function testReloadComponent() {
        $component = new CM_Component_Notfound([]);
        $viewResponse = new CM_Frontend_ViewResponse($component);
        $request = $this->createRequestAjax($component, 'reloadComponent', ['foo' => 'bar'], $viewResponse);
        $response = $this->processRequest($request);
        $this->assertViewResponseSuccess($response);

        $frontend = $response->getRender()->getGlobalResponse();
        $oldAutoId = $viewResponse->getAutoId();
        $newAutoId = $frontend->getTreeRoot()->getValue()->getAutoId();

        $expected = <<<EOL
cm.window.appendHidden("<div id=\\"$newAutoId\\" class=\\"CM_Component_Notfound CM_Component_Abstract CM_View_Abstract\\">Sorry, this page was not found. It has been removed or never existed.\\n<\/div>");
cm.views["$newAutoId"] = new CM_Component_Notfound({el:$("#$newAutoId").get(0),params:{"foo":"bar"}});
cm.views["$oldAutoId"].getParent().registerChild(cm.views["$newAutoId"]);
cm.views["$oldAutoId"].replaceWithHtml(cm.views["$newAutoId"].\$el);
cm.views["$newAutoId"]._ready();
EOL;
        $this->assertSame($expected, $frontend->getJs());
    }

    public function testReloadComponentAdditionalParams() {
        $entity = CM_Model_Entity_Mock2::createStatic();
        $config = CM_Config::get();
        $config->CM_Model_Abstract->types[CM_Model_Entity_Mock2::getTypeStatic()] = get_class($entity);
        $config->CM_Model_Entity_Mock2 = new stdClass();
        $config->CM_Model_Entity_Mock2->type = CM_Model_Entity_Mock2::getTypeStatic();

        $component = new CM_Component_Mock();
        $viewResponse = new CM_Frontend_ViewResponse($component);
        $request = $this->createRequestAjax($component, 'reloadComponent', ['entity' => $entity], $viewResponse);
        $response = $this->processRequest($request);

        $this->assertViewResponseSuccess($response);
        $frontend = $response->getRender()->getGlobalResponse();
        $oldAutoId = $viewResponse->getAutoId();
        $newAutoId = $frontend->getTreeRoot()->getValue()->getAutoId();

        $expected = <<<EOL
cm.window.appendHidden("<div id=\\"$newAutoId\\" class=\\"CM_Component_Mock CM_Component_Notfound CM_Component_Abstract CM_View_Abstract\\">Sorry, this page was not found. It has been removed or never existed.\\n<\/div>");
cm.views["$newAutoId"] = new CM_Component_Mock({el:$("#$newAutoId").get(0),params:{"entity":{"_class":"CM_Model_Entity_Mock2","_type":123,"_id":{"id":"1"},"id":1,"path":null}}});
cm.views["$oldAutoId"].getParent().registerChild(cm.views["$newAutoId"]);
cm.views["$oldAutoId"].replaceWithHtml(cm.views["$newAutoId"].\$el);
cm.views["$newAutoId"]._ready();
EOL;
        $this->assertSame($expected, $frontend->getJs());
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

    public function getLayout(CM_Frontend_Environment $environment, $layoutName = null) {
        return new CM_Layout_Mock1();
    }
}

class CM_Page_View_Ajax_Test_MockRedirectSelf extends CM_Page_Abstract {

    public function prepareResponse(CM_Frontend_Environment $environment, CM_Http_Response_Page $response) {
        $count = $this->_params->getInt('count');
        if ($count > 0) {
            $response->redirect($this, ['count' => --$count]);
        }
    }

    public function getLayout(CM_Frontend_Environment $environment, $layoutName = null) {
        return new CM_Layout_Mock1();
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
