<?php

class CM_Http_Response_RPCTest extends CMTest_TestCase {

    public function testProcessExceptionCatching() {
        CM_Config::get()->CM_Http_Response_RPC->catchPublicExceptions = true;
        CM_Config::get()->CM_Http_Response_RPC->exceptionsToCatch = ['CM_Exception_Nonexistent' => []];
        $request = $this->mockObject('CM_Http_Request_Abstract', ['/rpc/' . CM_Site_Abstract::factory()->getType() . '/foo']);
        $request->mockMethod('getQuery')->set(function () {
            throw new CM_Exception_Invalid('foo', null, ['messagePublic' => 'bar']);
        });
        /** @var CM_Http_Request_Abstract $response */
        $response = new CM_Http_Response_RPC($request, CMTest_TH::getServiceManager());

        CMTest_TH::callProtectedMethod($response, '_process');
        $responseData = CM_Params::jsonDecode($response->getContent());

        $this->assertSame(
            ['error' =>
                 ['type'     => 'CM_Exception_Invalid',
                  'msg'      => 'bar',
                  'isPublic' => true
                 ]
            ], $responseData);

        $request->mockMethod('getQuery')->set(function () {
            throw new CM_Exception_Nonexistent('foo');
        });
        $response = new CM_Http_Response_RPC($request, CMTest_TH::getServiceManager());

        CMTest_TH::callProtectedMethod($response, '_process');
        $responseData = CM_Params::jsonDecode($response->getContent());

        $this->assertSame(
            ['error' =>
                 ['type'     => 'CM_Exception_Nonexistent',
                  'msg'      => 'Internal server error',
                  'isPublic' => false
                 ]
            ], $responseData);
    }
}
