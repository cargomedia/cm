<?php

class CM_Http_Response_View_AjaxTest extends CMTest_TestCase {

    public function testProcess() {
        $view = new CM_Component_Example();
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = $this->createRequestAjax($view, 'test', ['x' => 'foo']);
        $response = CM_Http_Response_View_Ajax::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();

        $this->assertJson($response->getContent());
    }

    public function testSuspiciousMethodException() {
        /** @var CM_View_Abstract|PHPUnit_Framework_MockObject_MockObject $view */
        $view = $this->getMockForAbstractClass('CM_View_Abstract');
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = $this->createRequestAjax($view, '_ad_!!!##', ['params' => 'foo']);
        $response = CM_Http_Response_View_Ajax::createFromRequest($request, $site, $this->getServiceManager());
        $exception = $this->catchException(function () use ($response) {
            $response->process();
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Illegal method', $exception->getMessage());
        $this->assertSame(['method' => '_ad_!!!##'], $exception->getMetaInfo());
    }

    public function testNonexistentAjaxMethodException() {
        /** @var CM_View_Abstract|PHPUnit_Framework_MockObject_MockObject $view */
        $view = $this->getMockForAbstractClass('CM_View_Abstract');
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = $this->createRequestAjax($view, 'someReallyBadMethod', ['params' => 'foo']);
        $response = CM_Http_Response_View_Ajax::createFromRequest($request, $site, $this->getServiceManager());
        $exception = $this->catchException(function () use ($response) {
            $response->process();
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Method not found', $exception->getMessage());
        $this->assertSame(['method' => 'ajax_someReallyBadMethod'], $exception->getMetaInfo());
    }
}
