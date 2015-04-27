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
        $request = new CM_Http_Request_Post('/rpc/' . CM_Site_Abstract::factory()->getType(), null, null, $body);
        $response = new CM_Http_Response_RPC($request, $this->getServiceManager());
        $response->process();

        $responseData = CM_Params::jsonDecode($response->getContent());
        $this->assertSame([
            'success' => [
                'result' => 5
            ]
        ], $responseData);
    }

    public function testProcessExceptionCatching() {
        CM_Config::get()->CM_Http_Response_RPC->catchPublicExceptions = true;
        CM_Config::get()->CM_Http_Response_RPC->exceptionsToCatch = ['CM_Exception_Nonexistent' => []];
        $request = $this->mockObject('CM_Http_Request_Abstract', ['/rpc/' . CM_Site_Abstract::factory()->getType() . '/foo']);
        $request->mockMethod('getQuery')->set(function () {
            throw new CM_Exception_Invalid('foo', null, ['messagePublic' => 'bar']);
        });
        /** @var CM_Http_Request_Abstract|\Mocka\AbstractClassTrait $request */
        $response = new CM_Http_Response_RPC($request, $this->getServiceManager());

        $response->process();
        $responseData = CM_Params::jsonDecode($response->getContent());

        $this->assertSame([
            'error' => [
                'type'     => 'CM_Exception_Invalid',
                'msg'      => 'bar',
                'isPublic' => true,
            ]
        ], $responseData);

        $request->mockMethod('getQuery')->set(function () {
            throw new CM_Exception_Nonexistent('foo');
        });
        $response = new CM_Http_Response_RPC($request, CMTest_TH::getServiceManager());

        $response->process();
        $responseData = CM_Params::jsonDecode($response->getContent());

        $this->assertSame(
            ['error' =>
                 ['type'     => 'CM_Exception_Nonexistent',
                  'msg'      => 'Internal server error',
                  'isPublic' => false
                 ]
            ], $responseData);
    }

    public function testProcessingWithoutMethod() {
        $body = CM_Params::jsonEncode(['method' => null]);
        $request = new CM_Http_Request_Post('/rpc/' . CM_Site_Abstract::factory()->getType(), null, null, $body);
        $response = new CM_Http_Response_RPC($request, $this->getServiceManager());
        $response->process();

        $responseData = CM_Params::jsonDecode($response->getContent());
        $this->assertSame([
            'error' => [
                'type'     => 'CM_Exception_InvalidParam',
                'msg'      => 'Internal server error',
                'isPublic' => false,
            ]
        ], $responseData);
    }

    public function testProcessingInvalidMethod() {
        $body = CM_Params::jsonEncode(['method' => 'foo']);
        $request = new CM_Http_Request_Post('/rpc/' . CM_Site_Abstract::factory()->getType(), null, null, $body);
        $response = new CM_Http_Response_RPC($request, $this->getServiceManager());
        $response->process();

        $responseData = CM_Params::jsonDecode($response->getContent());
        $this->assertSame([
            'error' => [
                'type'     => 'CM_Exception_InvalidParam',
                'msg'      => 'Internal server error',
                'isPublic' => false,
            ]
        ], $responseData);
    }

    public static function rpc_add($foo, $bar) {
        return $foo + $bar;
    }
}
