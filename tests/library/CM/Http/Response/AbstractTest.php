<?php

class CM_Http_Response_AbstractTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testFactory() {
        $responses = array();
        $responses['/captcha'] = 'CM_Http_Response_Captcha';
        $responses['/emailtracking'] = 'CM_Http_Response_EmailTracking';
        $responses['/rpc'] = 'CM_Http_Response_RPC';
        $responses['/upload'] = 'CM_Http_Response_Upload';
        $responses['/library-css'] = 'CM_Http_Response_Resource_Css_Library';
        $responses['/vendor-css'] = 'CM_Http_Response_Resource_Css_Vendor';
        $responses['/library-js'] = 'CM_Http_Response_Resource_Javascript_Library';
        $responses['/vendor-js'] = 'CM_Http_Response_Resource_Javascript_Vendor';
        $responses['/layout'] = 'CM_Http_Response_Resource_Layout';
        $responses['/ajax'] = 'CM_Http_Response_View_Ajax';
        $responses['/form'] = 'CM_Http_Response_View_Form';
        $responses['/homepage'] = 'CM_Http_Response_Page';

        foreach ($responses as $path => $expectedResponse) {
            $request = new CM_Http_Request_Post($path . '/null/timestamp', null, null, '');
            $this->assertInstanceOf($expectedResponse, CM_Http_Response_Abstract::factory($request, $this->getServiceManager()));
        }
    }

    public function testSetDeleteCookie() {
        $request = new CM_Http_Request_Post('/foo/null');
        $response = CM_Http_Response_Abstract::factory($request, $this->getServiceManager());
        $time = time();
        $timeString = date('D\, d\-M\-Y h:i:s e', $time);

        $response->setCookie('foo', 'bar', $time);
        $response->setCookie('bar', 'bad!=();');
        $headers = $response->getHeaders();
        $this->assertSame('Set-Cookie: foo=bar; Expires=' . $timeString . '; Path=/', $headers[0]);
        $this->assertSame('Set-Cookie: bar=bad%21%3D%28%29%3B; Path=/', $headers[1]);

        $response->deleteCookie('foo');
        $headers = $response->getHeaders();
        $this->assertSame('Set-Cookie: foo=; Expires=Thu, 01-Jan-1970 12:00:01 UTC; Path=/', $headers[0]);
    }

    public function testRunWithCatching() {
        $response = $this->mockClass('CM_Http_Response_Abstract')->newInstanceWithoutConstructor();
        $className = get_class($response);
        CM_Config::get()->$className = new stdClass();
        CM_Config::get()->$className->exceptionsToCatch = [
            'CM_Exception_Nonexistent' => ['log' => 'CM_Paging_Log_NotFound', 'foo' => 'bar'],
            'CM_Exception_InvalidParam' => ['log' => null]
        ];
        $exceptionCodeExecutionCounter = 0;
        $errorCode = function(CM_Exception_Nonexistent $ex, $errorOptions) use (&$exceptionCodeExecutionCounter) {
            $this->assertSame('bar', $errorOptions['foo']);
            $exceptionCodeExecutionCounter++;
        };
        $this->assertSame(0, $exceptionCodeExecutionCounter);
        $this->assertCount(0, new CM_Paging_Log_NotFound());
        CMTest_TH::callProtectedMethod($response, '_runWithCatching', [function() {}, $errorCode]);
        $this->assertSame(0, $exceptionCodeExecutionCounter);
        $this->assertCount(0, new CM_Paging_Log_NotFound());
        CMTest_TH::callProtectedMethod($response, '_runWithCatching', [function() {throw new CM_Exception_Nonexistent();}, $errorCode]);
        $this->assertSame(1, $exceptionCodeExecutionCounter);
        $this->assertCount(1, new CM_Paging_Log_NotFound());

        $errorCode = function(CM_Exception_InvalidParam $ex, $errorOptions) use (&$exceptionCodeExecutionCounter) {
            $exceptionCodeExecutionCounter++;
        };
        CMTest_TH::callProtectedMethod($response, '_runWithCatching', [function() {throw new CM_Exception_InvalidParam();}, $errorCode]);
        $this->assertSame(2, $exceptionCodeExecutionCounter);
        $this->assertCount(1, new CM_Paging_Log_NotFound());
    }
}
