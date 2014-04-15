<?php

class CM_Response_View_AbstractTest extends CMTest_TestCase {

    public function testLoadPage() {
        $viewer = CMTest_TH::createUser();
        $response = $this->getResponseAjax('loadPage', 'CM_Page_View_Ajax_Test_Mock', array('path' => CM_Page_View_Ajax_Test_Mock::getPath()), $viewer);
        $this->assertAjaxResponseSuccess($response);
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
        $response = $this->getResponseAjax('loadPage', 'CM_Page_View_Ajax_Test_MockRedirect', array('path' => CM_Page_View_Ajax_Test_MockRedirect::getPath()));
        $this->assertAjaxResponseSuccess($response, array('redirectExternal' => 'http://www.foo.bar'));
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
