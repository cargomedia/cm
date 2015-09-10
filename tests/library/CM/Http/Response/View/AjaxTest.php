<?php

class CM_Http_Response_View_AjaxTest extends CMTest_TestCase {

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Illegal method: `_ad_!!!##`
     */
    public function testSuspiciousMethodException() {
        /** @var CM_View_Abstract|PHPUnit_Framework_MockObject_MockObject $view */
        $view = $this->getMockForAbstractClass('CM_View_Abstract');

        $request = $this->createRequestAjax($view, '_ad_!!!##', ['params' => 'foo']);
        $response = new CM_Http_Response_View_Ajax($request, $this->getServiceManager());
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
        $response = new CM_Http_Response_View_Ajax($request, $this->getServiceManager());
        $response->process();
    }
}
