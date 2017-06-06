<?php

class CM_View_AbstractTest extends CMTest_TestCase {

    public function testConstructor() {
        $params = new CM_Params(['foo' => 'bar', 'baz' => 'cex'], false);
        /** @var CM_View_Abstract $view */
        $view = $this->getMockForAbstractClass('CM_View_Abstract', ['params' => $params]);

        $viewParams = $view->getParams();
        $this->assertInstanceOf('CM_Params', $viewParams);
        $this->assertEquals($params, $viewParams);
    }

    public function testAjax_loadComponent() {
        /** @var CM_View_Abstract $view */
        $view = $this->getMockForAbstractClass('CM_View_Abstract');
        $site = $this->getMockSite();
        $request = $this->createRequestAjax($view, 'someMethod', ['foo' => 'bar']);

        $mockClassResponse = $this->mockClass('CM_Http_Response_View_Ajax');
        $mockLoadComponentMethod = $mockClassResponse->mockMethod('loadComponent');
        $mockLoadComponentMethod->set(function ($className, CM_Params $params) {
            $this->assertSame('CM_Component_Abstract', $className);
            $this->assertEquals([], $params->getParamsDecoded());
        });
        /** @var CM_Http_Response_View_Ajax|\Mocka\AbstractClassTrait $mockResponse */
        $mockResponse = $mockClassResponse->newInstance([$request, $site, $this->getServiceManager()]);
        $componentHandler = new CM_Frontend_JavascriptContainer_View();

        $view->ajax_loadComponent(new CM_Params(['className' => 'CM_Component_Abstract'], false), $componentHandler, $mockResponse);

        $this->assertSame(1, $mockLoadComponentMethod->getCallCount());
    }

    public function testAjax_loadComponentBadClass() {
        /** @var CM_View_Abstract $view */
        $view = $this->getMockForAbstractClass('CM_View_Abstract');
        $exception = $this->catchException(function () use ($view) {
            $this->getResponseAjax($view, 'loadComponent', ['className' => 'absentClassName']);
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Class not found', $exception->getMessage());
        $this->assertSame(['className' => 'absentClassName'], $exception->getMetaInfo());
    }
}
