<?php

class CM_View_AbstractTest extends CMTest_TestCase {

    public function testConstructor() {
        $params = new CM_Params(['foo' => 'bar', 'baz' => 'cex']);
        /** @var CM_View_Abstract $view */
        $view = $this->getMockForAbstractClass('CM_View_Abstract', ['params' => $params]);

        $this->assertInstanceOf('CM_View_Abstract', $view);
        $viewParams = $view->getParams();
        $this->assertInstanceOf('CM_Params', $viewParams);
        $this->assertEquals($params, $viewParams);
    }

    public function testAjax_loadComponent() {
        /** @var CM_View_Abstract $view */
        $view = $this->getMockForAbstractClass('CM_View_Abstract');
        $request = $this->createRequestAjax($view, 'someMethod', ['foo' => 'bar']);

        $mockClassResponse = $this->mockClass('CM_Http_Response_View_Ajax');
        $mockLoadComponentMethod = $mockClassResponse->mockMethod('loadComponent');
        $mockLoadComponentMethod->set(function (CM_Params $params) {
            $this->assertEquals(['className' => 'CM_Component_Abstract'], $params->getParamsDecoded());
        });
        /** @var CM_Http_Response_View_Ajax|\Mocka\AbstractClassTrait $mockResponse */
        $mockResponse = $mockClassResponse->newInstance(['request' => $request, 'serviceManager' => $this->getServiceManager()]);
        $componentHandler = new CM_Frontend_JavascriptContainer_View();

        $view->ajax_loadComponent(new CM_Params(['className' => 'CM_Component_Abstract']), $componentHandler, $mockResponse);

        $this->assertSame(1, $mockLoadComponentMethod->getCallCount());
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Class not found: `absentClassName`
     */
    public function testAjax_loadComponentBadClass() {
        /** @var CM_View_Abstract $view */
        $view = $this->getMockForAbstractClass('CM_View_Abstract');
        $request = $this->createRequestAjax($view, 'someMethod', ['foo' => 'bar']);
        $response = new CM_Http_Response_View_Ajax($request, $this->getServiceManager());
        $componentHandler = new CM_Frontend_JavascriptContainer_View();

        $view->ajax_loadComponent(new CM_Params(['className' => 'absentClassName']), $componentHandler, $response);
    }
}
