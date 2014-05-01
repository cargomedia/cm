<?php

class CM_Response_View_AbstractTest extends CMTest_TestCase {

    public function testLoadPage() {
        $viewer = CMTest_TH::createUser();
        $response = $this->getResponseAjax('loadPage', 'CM_Page_View_Ajax_Test_Mock', array('path' => CM_Page_View_Ajax_Test_Mock::getPath()), $viewer);
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
        $response = $this->getResponseAjax('loadPage', 'CM_Page_View_Ajax_Test_Mock', array('path' => CM_Page_View_Ajax_Test_MockRedirect::getPath()));
        $this->assertViewResponseSuccess($response, array('redirectExternal' => 'http://www.foo.bar'));
    }

    public function testLoadComponent() {
        $response = $this->getResponseAjax('loadComponent', 'CM_Component_Graph', array('className' => 'CM_Component_Graph', 'series' => []));
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

    public function prepareResponse(CM_Response_Page $response) {
        $response->redirectUrl('http://www.foo.bar');
    }
}

class CM_Page_View_Ajax_Test_Mock extends CM_Page_Abstract {

    public function getLayout(CM_Site_Abstract $site, $layoutName = null) {
        $layoutname = 'Mock1';
        $classname = self::_getClassNamespace() . '_Layout_' . $layoutname;
        return new $classname();
    }
}

class CM_Layout_Mock1 extends CM_Layout_Abstract {

}
