<?php

class CM_Http_Response_View_AjaxTest extends CMTest_TestCase {

    public function testProcess() {
        $view = new CM_Component_Example();
        $request = $this->createRequestAjax($view, 'test', ['x' => 'foo']);
        $response = CM_Http_Response_View_Ajax::createFromRequest($request, $this->getServiceManager());
        $response->process();

        $this->assertJson($response->getContent());
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Illegal method: `_ad_!!!##`
     */
    public function testSuspiciousMethodException() {
        /** @var CM_View_Abstract|PHPUnit_Framework_MockObject_MockObject $view */
        $view = $this->getMockForAbstractClass('CM_View_Abstract');

        $request = $this->createRequestAjax($view, '_ad_!!!##', ['params' => 'foo']);
        $response = CM_Http_Response_View_Ajax::createFromRequest($request, $this->getServiceManager());
        $response->process();
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Method not found: `ajax_someReallyBadMethod`
     */
    public function testNonexistentAjaxMethodException() {
        /** @var CM_View_Abstract|PHPUnit_Framework_MockObject_MockObject $view */
        $view = $this->getMockForAbstractClass('CM_View_Abstract');

        $request = $this->createRequestAjax($view, 'someReallyBadMethod', ['params' => 'foo']);
        $response = CM_Http_Response_View_Ajax::createFromRequest($request, $this->getServiceManager());
        $response->process();
    }
}
