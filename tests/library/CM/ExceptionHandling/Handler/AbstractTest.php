<?php

class CM_ExceptionHandling_Handler_AbstractTest extends CMTest_TestCase {

    public function testLogException() {
        $msg = 'My Exception';
        $metaInfo = ['foo' => 12, 'bar' => [1, 2, 3]];

        /** @var CM_Paging_Log_Error|\Mocka\ClassMock $log */
        $log = $this->mockClass('CM_Paging_Log_Error')->newInstanceWithoutConstructor();
        $addMethod = $log->mockMethod('add')->set(function ($msgActual, array $metaInfoActual) use ($msg, $metaInfo) {
            $this->assertSame($msg, $msgActual);
            $this->assertSame($metaInfo, $metaInfoActual);
        });

        /** @var CM_Exception|PHPUnit_Framework_MockObject_MockObject $exception */
        $exception = $this->getMockBuilder('CM_Exception')->setMethods(['getMessage', 'getLog', 'getMetaInfo'])->getMock();
        $exception->expects($this->any())->method('getMessage')->will($this->returnValue($msg));
        $exception->expects($this->any())->method('getLog')->will($this->returnValue($log));
        $exception->expects($this->any())->method('getMetaInfo')->will($this->returnValue($metaInfo));

        /** @var CM_ExceptionHandling_Handler_Abstract|\Mocka\ClassMock $exceptionHandler */
        $exceptionHandler = $this->mockObject('CM_ExceptionHandling_Handler_Abstract');
        $exceptionHandler->logException($exception);

        $this->assertSame(1, $addMethod->getCallCount());
    }

    public function testLogExceptionFileLog() {
        $errorLog = CM_Bootloader::getInstance()->getDirTmp() . uniqid();
        $log = $this->getMockBuilder('CM_Paging_Log_Error')->setMethods(array('add'))->disableOriginalConstructor()->getMock();
        $log->expects($this->any())->method('add')->will($this->throwException(new Exception('foo')));

        $exception = $this->getMockBuilder('CM_Exception')->setMethods(array('getLog', 'getMetaInfo'))->getMock();
        $exception->expects($this->any())->method('getLog')->will($this->returnValue($log));
        $exception->expects($this->any())->method('getMetaInfo')->will($this->returnValue(array()));

        $method = CMTest_TH::getProtectedMethod('CM_ExceptionHandling_Handler_Abstract', 'logException');
        $exceptionHandler = $this->getMockBuilder('CM_ExceptionHandling_Handler_Abstract')->setMethods(array('_getLogFile'))->getMockForAbstractClass();
        $exceptionHandler->expects($this->any())->method('_getLogFile')->will($this->returnValue(new CM_File($errorLog)));

        $this->assertFileNotExists($errorLog);
        $method->invoke($exceptionHandler, $exception);

        $logContents = file_get_contents($errorLog);
        $this->assertNotEmpty($logContents);
        $this->assertContains('### Cannot log error: ', $logContents);
        $this->assertContains('### Original Exception: ', $logContents);
    }

    public function testPrintException() {
        $errorException = new CM_Exception();
        $nativeException = new Exception();
        $fatalException = new CM_Exception(null, null, CM_Exception::FATAL);

        $exceptionHandler = $this->getMockBuilder('CM_ExceptionHandling_Handler_Abstract')
            ->setMethods(array('logException', '_printException'))->getMockForAbstractClass();
        $exceptionHandler->expects($this->at(1))->method('_printException')->with($errorException);
        $exceptionHandler->expects($this->at(3))->method('_printException')->with($nativeException);
        $exceptionHandler->expects($this->at(5))->method('_printException')->with($fatalException);
        /** @var CM_ExceptionHandling_Handler_Abstract $exceptionHandler */

        $exceptionHandler->handleException($errorException);
        $exceptionHandler->handleException($nativeException);
        $exceptionHandler->handleException($fatalException);
    }

    public function testPrintExceptionPrintSeverity() {
        $errorException = new CM_Exception();
        $nativeException = new Exception();
        $fatalException = new CM_Exception(null, null, CM_Exception::FATAL);

        $exceptionHandler = $this->getMockBuilder('CM_ExceptionHandling_Handler_Abstract')
            ->setMethods(array('logException', '_printException'))->getMockForAbstractClass();
        $exceptionHandler->expects($this->any())->method('logException');
        $exceptionHandler->expects($this->at(2))->method('_printException')->with($nativeException);
        $exceptionHandler->expects($this->at(4))->method('_printException')->with($fatalException);
        /** @var CM_ExceptionHandling_Handler_Abstract $exceptionHandler */

        $exceptionHandler->setPrintSeverityMin(CM_Exception::FATAL);
        $exceptionHandler->handleException($errorException);
        $exceptionHandler->handleException($nativeException);
        $exceptionHandler->handleException($fatalException);
    }

    /**
     * @expectedException ErrorException
     * @expectedExceptionMessage E_USER_ERROR: Raw error message
     */
    public function testHandleErrorRaw() {
        trigger_error('Raw error message', E_USER_ERROR);
    }
}
