<?php

class CM_Response_View_AbstractTest extends CMTest_TestCase {

    public function testLoadPage() {
        $viewer = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment(null, $viewer);
        $scopeView = new CM_Frontend_ViewResponse(new CM_Page_View_Ajax_Test_Mock());
        $response = $this->getResponseAjax('loadPage', ['path' => CM_Page_View_Ajax_Test_Mock::getPath()], $scopeView, null, $environment);

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
        $scopeView = new CM_Frontend_ViewResponse(new CM_Page_View_Ajax_Test_Mock());
        $response = $this->getResponseAjax('loadPage', ['path' => CM_Page_View_Ajax_Test_MockRedirect::getPath()], $scopeView);
        $this->assertViewResponseSuccess($response, array('redirectExternal' => 'http://www.foo.bar'));
    }

    public function testLoadComponent() {
        $scopeView = new CM_Frontend_ViewResponse(new CM_Component_Graph());
        $response = $this->getResponseAjax('loadComponent', ['className' => 'CM_Component_Graph', 'series' => []], $scopeView);
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
