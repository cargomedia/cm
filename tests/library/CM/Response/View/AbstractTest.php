<?php

class CM_Response_View_AbstractTest extends CMTest_TestCase {

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
        $config = CM_Config::get();
        $config->CM_Model_Abstract->types[CM_Model_Entity_Mock2::getTypeStatic()] = 'CM_Model_Entity_Mock2';
        $config->CM_Model_Entity_Mock2 = new stdClass();
        $config->CM_Model_Entity_Mock2->type = CM_Model_Entity_Mock2::getTypeStatic();

        $user = CM_Model_User::createStatic();
        $entity = CM_Model_Entity_Mock2::createStatic(['userId' => $user->getId(), 'foo' => 'bar1']);
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
}

class CM_Page_View_Ajax_Test_MockRedirect extends CM_Page_Abstract {

    public function prepareResponse(CM_Frontend_Environment $environment, CM_Response_Page $response) {
        $response->redirectUrl('http://www.foo.bar');
    }
}

class CM_Page_View_Ajax_Test_Mock extends CM_Page_Abstract {

    public function getLayout(CM_Frontend_Environment $environment, $layoutName = null) {
        $layoutname = 'Mock1';
        $classname = self::_getClassNamespace() . '_Layout_' . $layoutname;
        return new $classname();
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
        $entity = new self(1);
        $entity->_set($data);
        return $entity;
    }

    public static function getTypeStatic() {
        return 123;
    }
}
