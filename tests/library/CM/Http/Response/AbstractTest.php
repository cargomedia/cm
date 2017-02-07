<?php

class CM_Http_Response_AbstractTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testSetDeleteCookie() {
        /** @var CM_Http_Response_Abstract|\Mocka\AbstractClassTrait $response */
        $response = $this->mockClass('CM_Http_Response_Abstract')->newInstanceWithoutConstructor();
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
        /** @var CM_Http_Response_Abstract|\Mocka\AbstractClassTrait $response */
        $response = $this->mockClass('CM_Http_Response_Abstract')->newInstanceWithoutConstructor();
        $response->mockMethod('getRequest')->set(new CM_Http_Request_Get('/foo/bar/'));
        $response->setServiceManager($this->getServiceManager());
        $className = get_class($response);

        // test logging and errorCallback-execution
        CM_Config::get()->$className = new stdClass();
        CM_Config::get()->$className->exceptionsToCatch = [
            'CM_Exception_Nonexistent'  => ['log' => true, 'level' => CM_Log_Logger::INFO, 'foo' => 'bar'],
            'CM_Exception_InvalidParam' => [],
        ];
        $exceptionCodeExecutionCounter = 0;
        $errorCode = function (CM_Exception_Nonexistent $ex, $errorOptions) use (&$exceptionCodeExecutionCounter) {
            $this->assertSame('bar', $errorOptions['foo']);
            $exceptionCodeExecutionCounter++;
        };
        $this->assertSame(0, $exceptionCodeExecutionCounter);
        $this->assertCount(0, new CM_Paging_Log([CM_Log_Logger::INFO]));
        CMTest_TH::callProtectedMethod($response, '_runWithCatching', [
            function () {
            }, $errorCode]);
        $this->assertSame(0, $exceptionCodeExecutionCounter);
        $this->assertCount(0, new CM_Paging_Log([CM_Log_Logger::INFO]));
        CMTest_TH::callProtectedMethod($response, '_runWithCatching', [
            function () {
                throw new CM_Exception_Nonexistent();
            }, $errorCode]);
        $this->assertSame(1, $exceptionCodeExecutionCounter);
        $this->assertCount(1, new CM_Paging_Log([CM_Log_Logger::INFO]));
        $errorCode = function (CM_Exception_InvalidParam $ex, $errorOptions) use (&$exceptionCodeExecutionCounter) {
            $exceptionCodeExecutionCounter++;
        };
        CMTest_TH::callProtectedMethod($response, '_runWithCatching', [
            function () {
                throw new CM_Exception_InvalidParam();
            }, $errorCode]);
        $this->assertSame(2, $exceptionCodeExecutionCounter);
        $this->assertCount(1, new CM_Paging_Log([CM_Log_Logger::INFO]));

        // test public/non-public exceptions not marked for catching

        $exceptionCodeExecutionCounter = 0;

        // public exception, no public exception catching
        CM_Config::get()->$className->exceptionsToCatch = [];
        $errorCode = function (CM_Exception_Nonexistent $ex, $errorOptions) use (&$exceptionCodeExecutionCounter) {
            $exceptionCodeExecutionCounter++;
            $this->assertTrue($ex->isPublic());
        };
        try {
            CMTest_TH::callProtectedMethod($response, '_runWithCatching', [
                function () {
                    throw new CM_Exception_Nonexistent('foo', null, null, [
                        'messagePublic' => new CM_I18n_Phrase('bar'),
                    ]);
                }, $errorCode]);
            $this->fail('Caught public exception with public exception catching disabled');
        } catch (CM_Exception_Nonexistent $ex) {
            $this->assertTrue($ex->isPublic());
            $this->assertSame('foo', $ex->getMessage());
        }
        $this->assertSame(0, $exceptionCodeExecutionCounter);

        // non-public exception, public exception catching
        CM_Config::get()->$className->catchPublicExceptions = true;
        try {
            CMTest_TH::callProtectedMethod($response, '_runWithCatching', [
                function () {
                    throw new CM_Exception_Nonexistent('foo');
                }, $errorCode]);
            $this->fail('Caught non-public exception that was not configured to be caught');
        } catch (CM_Exception_Nonexistent $ex) {
            $this->assertFalse($ex->isPublic());
            $this->assertSame('foo', $ex->getMessage());
        }
        $this->assertSame(0, $exceptionCodeExecutionCounter);

        // public exception, public exception catching
        try {
            $returnValue = CMTest_TH::callProtectedMethod($response, '_runWithCatching', [
                function () {
                    throw new CM_Exception_Nonexistent('foo', null, null, [
                        'messagePublic' => new CM_I18n_Phrase('bar'),
                    ]);
                }, $errorCode]);
            $this->assertNull($returnValue);
        } catch (CM_Exception_Nonexistent $ex) {
            $this->fail('Caught non-public exception');
        }
        $this->assertSame(1, $exceptionCodeExecutionCounter);

        // test child exception catching

        $response = $this->mockClass('CM_Http_Response_Abstract')->newInstanceWithoutConstructor();
        $className = get_class($response);
        CM_Config::get()->$className = new stdClass();
        CM_Config::get()->$className->exceptionsToCatch = [
            'CM_Exception' => []
        ];
        $exceptionCodeExecutionCounter = 0;
        $errorCode = function (CM_Exception $ex, $errorOptions) use (&$exceptionCodeExecutionCounter) {
            $exceptionCodeExecutionCounter++;
        };

        try {
            $returnValue = CMTest_TH::callProtectedMethod($response, '_runWithCatching', [
                function () {
                    throw new CM_Exception_Invalid('foo');
                }, $errorCode]);
            $this->assertNull($returnValue);
        } catch (CM_Exception_Nonexistent $ex) {
            $this->fail("Didn't catch the child of an exception that was configured to be caught");
        }

        $this->assertSame(1, $exceptionCodeExecutionCounter);
    }
}
