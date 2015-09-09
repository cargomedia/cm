<?php

class CM_Http_Response_View_AjaxTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Illegal method: `_ad_!!!##`
     */
    public function testSuspiciousMethodException() {
        $page = new CM_Page_View_Ajax_Test_Mock_Ajax();
        $request = $this->createRequestAjax($page, '_ad_!!!##', ['params' => 'foo']);
        $response = new CM_Http_Response_View_Ajax($request, $this->getServiceManager());
        $response->process();
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Method not found: `ajax_someReallyBadMethod`
     */
    public function testNonexistentAjaxMethodException() {
        $page = new CM_Page_View_Ajax_Test_Mock_Ajax();
        $request = $this->createRequestAjax($page, 'someReallyBadMethod', ['params' => 'foo']);
        $response = new CM_Http_Response_View_Ajax($request, $this->getServiceManager());
        $response->process();
    }
}

class CM_Page_View_Ajax_Test_Mock_Ajax extends CM_Page_Abstract {

    public function getLayout(CM_Frontend_Environment $environment, $layoutName = null) {
        return new CM_Layout_Mock1();
    }
}

