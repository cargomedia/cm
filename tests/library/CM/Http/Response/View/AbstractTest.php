<?php

class CM_Http_Response_View_AbstractTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testLoadPage() {
        $viewer = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment(null, $viewer);
        $component = new CM_Page_View_Ajax_Test_Mock();
        $response = $this->getResponseAjax($component, 'loadPage', ['path' => CM_Page_View_Ajax_Test_Mock::getPath()], $environment);

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

    public function testLoadPageRedirectExternal() {
        $response = $this->getResponseAjax(new CM_Page_View_Ajax_Test_Mock(), 'loadPage', ['path' => CM_Page_View_Ajax_Test_MockRedirect::getPath()]);
        $this->assertViewResponseSuccess($response, array('redirectExternal' => 'http://www.foo.bar'));
    }

    public function testLoadPageRedirectHost() {
        $siteRequest = CM_Site_Abstract::factory();
        $siteEnvironment = $this->getMockSite(null, null, ['url' => 'http://www.example.com']);
        CMTest_TH::createLanguage('en');
        $viewer = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment($siteEnvironment, $viewer);
        $component = new CM_Page_View_Ajax_Test_Mock();
        $response = $this->getResponseAjax($component, 'loadPage', ['path' => CM_Page_View_Ajax_Test_Mock::getPath()], $environment);

        $this->assertViewResponseSuccess($response);
        $responseDecoded = CM_Params::jsonDecode($response->getContent());
        $this->assertSame($siteRequest->getUrl() . CM_Page_View_Ajax_Test_Mock::getPath(), $responseDecoded['success']['data']['url']);
    }

    public function testLoadPageRedirectLanguage() {
        $site = CM_Site_Abstract::factory();
        CMTest_TH::createLanguage('en');
        $viewer = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment(null, $viewer);
        $component = new CM_Page_View_Ajax_Test_Mock();
        $response = $this->getResponseAjax($component, 'loadPage', ['path' => '/en' . CM_Page_View_Ajax_Test_Mock::getPath()], $environment);

        $this->assertViewResponseSuccess($response);
        $responseDecoded = CM_Params::jsonDecode($response->getContent());
        $this->assertSame($site->getUrl() . CM_Page_View_Ajax_Test_Mock::getPath(), $responseDecoded['success']['data']['url']);
    }

    public function testLoadPageTracking() {
        $page = new CM_Page_View_Ajax_Test_Mock();
        $request = $this->createRequestAjax($page, 'loadPage', ['path' => $page::getPath()]);
        $response = new CM_Http_Response_View_Ajax($request, $this->_getServiceManagerWithGA('ga123'));
        $response->process();

        $this->assertViewResponseSuccess($response);
        $responseContent = CM_Params::decode($response->getContent(), true);
        $this->assertContains('ga("send", "pageview", "' . $page::getPath() . '")', $responseContent['success']['data']['jsTracking']);
    }

    public function testLoadPageTrackingRedirect() {
        $page = new CM_Page_View_Ajax_Test_MockRedirectSelf();
        $request = $this->createRequestAjax($page, 'loadPage', ['path' => $page::getPath() . '?count=3']);
        $response = new CM_Http_Response_View_Ajax($request, $this->_getServiceManagerWithGA('ga123'));
        $response->process();

        $this->assertViewResponseSuccess($response);
        $responseContent = CM_Params::decode($response->getContent(), true);
        $jsTracking = $responseContent['success']['data']['jsTracking'];
        $this->assertSame(1, substr_count($jsTracking, 'ga("send", "pageview"'));
        $this->assertContains('ga("send", "pageview", "' . $page::getPath() . '?count=0")', $jsTracking);
    }

    public function testLoadPageTrackingError() {
        CM_Config::get()->CM_Http_Response_Page->catch['CM_Exception_Nonexistent'] = CM_Page_View_Ajax_Test_Mock::getPath();

        $page = new CM_Page_View_Ajax_Test_Mock();
        $request = $this->createRequestAjax($page, 'loadPage', ['path' => '/iwhdfkjlsh']);
        $response = new CM_Http_Response_View_Ajax($request, $this->_getServiceManagerWithGA('ga123'));
        $response->process();

        $this->assertViewResponseSuccess($response);
        $responseContent = CM_Params::decode($response->getContent(), true);
        $jsTracking = $responseContent['success']['data']['jsTracking'];
        $html = $responseContent['success']['data']['html'];

        $this->assertSame(1, substr_count($jsTracking, 'ga("send", "pageview"'));
        $this->assertContains('ga("send", "pageview", "/iwhdfkjlsh")', $jsTracking);
        $this->assertContains('CM_Page_View_Ajax_Test_Mock', $html);
    }

    public function testReloadComponent() {
        $component = new CM_Component_Notfound([]);
        $scopeView = new CM_Frontend_ViewResponse($component);
        $request = $this->createRequestAjax($component, 'reloadComponent', ['foo' => 'bar'], $scopeView, $scopeView);
        $response = $this->processRequest($request);
        $this->assertViewResponseSuccess($response);

        $frontend = $response->getRender()->getGlobalResponse();
        $oldAutoId = $scopeView->getAutoId();
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
        $scopeView = new CM_Frontend_ViewResponse($component);
        $request = $this->createRequestAjax($component, 'reloadComponent', ['entity' => $entity], $scopeView, $scopeView);
        $response = $this->processRequest($request);

        $this->assertViewResponseSuccess($response);
        $frontend = $response->getRender()->getGlobalResponse();
        $oldAutoId = $scopeView->getAutoId();
        $newAutoId = $frontend->getTreeRoot()->getValue()->getAutoId();

        $expected = <<<EOL
cm.window.appendHidden("<div id=\\"$newAutoId\\" class=\\"CM_Component_Mock CM_Component_Notfound CM_Component_Abstract CM_View_Abstract\\">Sorry, this page was not found. It has been removed or never existed.\\n<\/div>");
cm.views["$newAutoId"] = new CM_Component_Mock({el:$("#$newAutoId").get(0),params:{"entity":{"_type":123,"_id":{"id":"1"},"id":1,"path":null,"_class":"CM_Model_Entity_Mock2"}}});
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
        $html = (new CM_Dom_NodeList($successContent['data']['html']))->find('.CM_Component_Abstract');
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
        $serviceManager->register('googleanalytics', 'CMService_GoogleAnalytics_Client', [$code]);
        $serviceManager->register('trackings', 'CM_Service_Trackings', [['googleanalytics']]);
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
