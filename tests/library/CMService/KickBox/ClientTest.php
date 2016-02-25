<?php

class CMService_KickBox_ClientTest extends CMTest_TestCase {

    public function testMalformedEmailAddress() {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponseBody'), array('', true, true, 0.2));
        $kickBoxMock->expects($this->never())->method('_getResponseBody');
        /** @var CMService_KickBox_Client $kickBoxMock */
        $this->assertFalse($kickBoxMock->isValid('invalid email@example.com'));
    }

    public function testNoCredits() {
        $responseBodyMock = null;
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseBodyMock);
        $this->assertTrue($kickBoxMock->isValid('testNoCredits@example.com'));
    }

    public function testInvalid() {
        $responseBodyMock = array('result' => 'invalid', 'disposable' => false, 'accept_all' => false, 'sendex' => 0.2);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseBodyMock);
        $this->assertFalse($kickBoxMock->isValid('testInvalid@example.com'));
        $kickBoxMock = $this->_getKickBoxMock(false, true, 0.2, $responseBodyMock);
        $this->assertTrue($kickBoxMock->isValid('testInvalid@example.com'));
    }

    public function testDisposable() {
        $responseBodyMock = array('result' => 'valid', 'disposable' => true, 'accept_all' => false, 'sendex' => 0.2);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseBodyMock);
        $this->assertFalse($kickBoxMock->isValid('testDisposable@example.com'));
        $kickBoxMock = $this->_getKickBoxMock(true, false, 0.2, $responseBodyMock);
        $this->assertTrue($kickBoxMock->isValid('testDisposable@example.com'));
    }

    public function testAcceptAll() {
        $responseBodyMock = array('result' => 'valid', 'disposable' => false, 'accept_all' => true, 'sendex' => 0.19);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseBodyMock);
        $this->assertFalse($kickBoxMock->isValid('testAcceptAll@example.com'));
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.19, $responseBodyMock);
        $this->assertTrue($kickBoxMock->isValid('testAcceptAll@example.com'));
    }

    public function testUnknown() {
        $responseBodyMock = array('result' => 'unknown', 'disposable' => false, 'accept_all' => false, 'sendex' => 0.19);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseBodyMock);
        $this->assertFalse($kickBoxMock->isValid('testUnknown@example.com'));
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.19, $responseBodyMock);
        $this->assertTrue($kickBoxMock->isValid('testUnknown@example.com'));
    }

    public function testValid() {
        $responseBodyMock = array('result' => 'valid', 'disposable' => false, 'accept_all' => false, 'sendex' => 0.19);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseBodyMock);
        $this->assertTrue($kickBoxMock->isValid('testValid@example.com'));
    }

    public function testHandleExceptionNoCredits() {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponse', '_logException'), array('', true, false, 0));
        $exception = new Exception('No credits');
        $kickBoxMock->expects($this->once())->method('_getResponse')->will($this->throwException($exception));
        $kickBoxMock->expects($this->once())->method('_logException')->with($exception);
        /** @var CMService_KickBox_Client $kickBoxMock */
        $kickBoxMock->isValid('test@example.com');
    }

    public function testHandleInvalidResponseCodeNoCredits() {
        $responseMock = new \Kickbox\HttpClient\Response(array('result' => 'unknown'), 403, array('header' => 'value'));
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponse', '_logException'), array('', true, false, 0));
        $kickBoxMock->expects($this->once())->method('_getResponse')->will($this->returnValue($responseMock));
        $exception = new CM_Exception('Invalid KickBox email validation response', null, [
            'email'   => 'test@example.com',
            'code'    => 403,
            'headers' => array('header' => 'value'),
            'body'    => array('result' => 'unknown'),
        ]);
        $kickBoxMock->expects($this->once())->method('_logException')->with($exception);
        /** @var CMService_KickBox_Client $kickBoxMock */
        $kickBoxMock->isValid('test@example.com');
    }

    public function testLogExceptionTimeout() {
        $kickBoxMock = $this->mockObject('CMService_KickBox_Client', array('', true, false, 0));

        $exceptionHandlerMock = $this->mockClass('CM_ExceptionHandling_Handler_Cli')->newInstance();
        $printException = $exceptionHandlerMock->mockMethod('logException');

        $exceptionHandler = CM_Bootloader::getInstance()->getExceptionHandler();
        /** @var CM_ExceptionHandling_Handler_Abstract $exceptionHandlerMock */
        CM_Bootloader::getInstance()->setExceptionHandler($exceptionHandlerMock);
        $i = 0;
        foreach ([
                     '[curl] 28: Operation timed out after 1595 milliseconds with 0 out of -1 bytes received [url] https://api.kickbox.io/v1/verify?email=testLogExceptionTimeout%40example.com',
                     '[curl] 6: name lookup timed out [url] https://api.kickbox.io/v1/verify?email=testLogExceptionTimeout%40example.com',
                     '[curl] 28: Connection timed out after 7007 milliseconds [url] https://api.kickbox.io/v1/verify?email=testLogExceptionTimeout%40example.com',
                 ] as $exceptionMessage) {
            $kickBoxMock->mockMethod('_getResponse')->set(function () use ($exceptionMessage) {
                throw new RuntimeException($exceptionMessage);
            });
            $printException->set(function (Exception $exception) use ($exceptionMessage) {
                $this->assertTrue($exception instanceof CM_Exception);
                /** @var CM_Exception $exception */
                $this->assertSame($exceptionMessage, $exception->getMessage());
                $this->assertSame(CM_Exception::WARN, $exception->getSeverity());
            });

            /** @var CMService_KickBox_Client $kickBoxMock */
            $kickBoxMock->isValid('testLogExceptionTimeout@example.com');

            $this->assertSame(++$i, $printException->getCallCount());
        }
        CM_Bootloader::getInstance()->setExceptionHandler($exceptionHandler);
    }

    public function testLogExceptionOther() {
        $kickBoxMock = $this->mockObject('CMService_KickBox_Client', array('', true, false, 0));
        $exceptionMessage = '[curl] 6: Couldn\'t resolve host \'api.kickbox.io\' [url] https://api.kickbox.io/v1/verify?email=testLogExceptionOther%40example.com';
        $kickBoxMock->mockMethod('_getResponse')->set(function () use ($exceptionMessage) {
            throw new RuntimeException($exceptionMessage);
        });

        $exceptionHandlerMock = $this->mockClass('CM_ExceptionHandling_Handler_Cli')->newInstance();
        $printException = $exceptionHandlerMock->mockMethod('logException')->set(function (Exception $exception) {
            $this->assertTrue('RuntimeException' === get_class($exception));
        });
        $exceptionHandler = CM_Bootloader::getInstance()->getExceptionHandler();
        /** @var CM_ExceptionHandling_Handler_Abstract $exceptionHandlerMock */
        CM_Bootloader::getInstance()->setExceptionHandler($exceptionHandlerMock);

        /** @var CMService_KickBox_Client $kickBoxMock */
        $kickBoxMock->isValid('testLogExceptionOther@example.com');

        $this->assertSame(1, $printException->getCallCount());
        CM_Bootloader::getInstance()->setExceptionHandler($exceptionHandler);
    }

    /**
     * @param bool       $disallowInvalid
     * @param bool       $disallowDisposable
     * @param float      $disallowUnknownThreshold
     * @param array|null $responseBodyMock
     * @return CMService_KickBox_Client
     */
    protected function _getKickBoxMock($disallowInvalid, $disallowDisposable, $disallowUnknownThreshold, $responseBodyMock) {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponseBody'), array(
            '',
            $disallowInvalid,
            $disallowDisposable,
            $disallowUnknownThreshold
        ));
        $kickBoxMock->expects($this->once())->method('_getResponseBody')->will($this->returnValue($responseBodyMock));
        return $kickBoxMock;
    }
}
