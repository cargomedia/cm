<?php

class CM_Http_Response_RPCTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testProcessing() {
        $body = CM_Params::jsonEncode([
            'method' => 'CM_Http_Response_RPCTest.add',
            'params' => [2, 3],
        ]);
        $site = $this->getMockSite();
        $request = new CM_Http_Request_Post('/rpc', ['host' => $site->getHost()], null, $body);
        $response = CM_Http_Response_RPC::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();

        $responseData = CM_Params::jsonDecode($response->getContent());
        $this->assertSame(['result' => 5], $responseData['success']);
    }

    public function testProcessExceptionCatching() {
        CM_Config::get()->CM_Http_Response_RPC->catchPublicExceptions = true;
        CM_Config::get()->CM_Http_Response_RPC->exceptionsToCatch = ['CM_Exception_Nonexistent' => []];
        $site = $this->getMockSite();
        /** @var CM_Http_Request_Abstract|\Mocka\AbstractClassTrait $request */
        $request = $this->mockObject('CM_Http_Request_Abstract', ['/rpc', ['host' => $site->getHost()]]);
        $request->mockMethod('getQuery')->set(function () {
            throw new CM_Exception_Invalid('foo', null, null, [
                'messagePublic' => new CM_I18n_Phrase('bar'),
            ]);
        });
        $response = CM_Http_Response_RPC::createFromRequest($request, $site, $this->getServiceManager());

        $response->process();
        $responseData = CM_Params::jsonDecode($response->getContent());

        $this->assertSame([
            'type'     => 'CM_Exception_Invalid',
            'msg'      => 'bar',
            'isPublic' => true,
            'metaInfo' => [],
        ], $responseData['error']);

        $request->mockMethod('getQuery')->set(function () {
            throw new CM_Exception_Nonexistent('foo');
        });
        $response = new CM_Http_Response_RPC($request, $site, CMTest_TH::getServiceManager());

        $response->process();
        $responseData = CM_Params::jsonDecode($response->getContent());

        $this->assertSame([
            'type'     => 'CM_Exception_Nonexistent',
            'msg'      => 'Internal server error',
            'isPublic' => false,
            'metaInfo' => [],
        ], $responseData['error']);
    }

    public function testProcessingWithoutMethod() {
        $body = CM_Params::jsonEncode(['method' => null]);
        $site = $this->getMockSite();
        $request = new CM_Http_Request_Post('/rpc', ['host' => $site->getHost()], null, $body);
        $response = CM_Http_Response_RPC::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();

        $responseData = CM_Params::jsonDecode($response->getContent());
        $this->assertSame([
            'type'     => 'CM_Exception_InvalidParam',
            'msg'      => 'Internal server error',
            'isPublic' => false,
            'metaInfo' => [],
        ], $responseData['error']);
    }

    public function testProcessingInvalidMethod() {
        $body = CM_Params::jsonEncode(['method' => 'foo']);
        $site = $this->getMockSite();
        $request = new CM_Http_Request_Post('/rpc', ['host' => $site->getHost()], null, $body);
        $response = CM_Http_Response_RPC::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();

        $responseData = CM_Params::jsonDecode($response->getContent());
        $this->assertSame([
            'type'     => 'CM_Exception_InvalidParam',
            'msg'      => 'Internal server error',
            'isPublic' => false,
            'metaInfo' => [],
        ], $responseData['error']);
    }

    public function testProcessReturnDeployVersion() {
        $body = CM_Params::jsonEncode([
            'method' => 'CM_Http_Response_RPCTest.add',
            'params' => [2, 3],
        ]);
        $site = $this->getMockSite();
        $request = new CM_Http_Request_Post('/rpc', null, null, $body);
        $response = CM_Http_Response_RPC::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();

        $responseDecoded = CM_Params::jsonDecode($response->getContent());
        $this->assertArrayHasKey('deployVersion', $responseDecoded);
        $this->assertSame(CM_App::getInstance()->getDeployVersion(), $responseDecoded['deployVersion']);
    }

    public static function rpc_add($foo, $bar) {
        return $foo + $bar;
    }
}
