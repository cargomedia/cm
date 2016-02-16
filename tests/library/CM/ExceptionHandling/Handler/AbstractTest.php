<?php

class CM_ExceptionHandling_Handler_AbstractTest extends CMTest_TestCase {

    public function testHandleException() {
        $expectedException = new Exception('foo');
        $handlerMock = $this->mockClass('CM_Log_Handler_Abstract')->newInstanceWithoutConstructor();

        /** @var CM_Log_Logger|\Mocka\ClassMock $logger */
        $logger = $this->mockClass('CM_Log_Logger')->newInstance([new CM_Log_Context(), [[$handlerMock]]]);
        $methodAddException = $logger->mockMethod('addException');
        $methodAddException->set(
            function (Exception $exception) use ($expectedException) {
                $this->assertEquals($expectedException, $exception);
            }
        );

        /** @var CM_Service_Manager|\Mocka\ClassMock $serviceManager */
        $serviceManager = $this->mockClass('CM_Service_Manager')->newInstance();
        $serviceManager->mockMethod('getLogger')->set($logger);

        /** @var CM_Log_Factory|\Mocka\ClassMock $loggerFactory */
        $loggerFactory = $this->mockClass('CM_Log_Factory')->newInstance();
        $loggerFactory->setServiceManager($serviceManager);

        /** @var CM_ExceptionHandling_Handler_Abstract $exceptionHandler */
        $exceptionHandler = $this->mockClass('CM_ExceptionHandling_Handler_Abstract')->newInstance([$loggerFactory]);
        $exceptionHandler->handleException($expectedException);
        $this->assertSame(1, $methodAddException->getCallCount());
    }

    public function testPrintException() {
        $errorException = new CM_Exception();
        $nativeException = new Exception();
        $fatalException = new CM_Exception(null, CM_Exception::FATAL);

        $exceptionHandler = $this->mockClass('CM_ExceptionHandling_Handler_Abstract')->newInstanceWithoutConstructor();
        $exceptionHandler->mockMethod('logException')->set(function () {
        });

        $printExceptionMock = $exceptionHandler->mockMethod('_printException')
            ->at(0, function (Exception $ex) use ($errorException) {
                $this->assertEquals($errorException, $ex);
            })
            ->at(1, function (Exception $ex) use ($nativeException) {
                $this->assertEquals($nativeException, $ex);
            })
            ->at(2, function (Exception $ex) use ($fatalException) {
                $this->assertEquals($fatalException, $ex);
            });

        /** @var CM_ExceptionHandling_Handler_Abstract $exceptionHandler */
        $exceptionHandler->handleException($errorException);
        $exceptionHandler->handleException($nativeException);
        $exceptionHandler->handleException($fatalException);

        $this->assertSame(3, $printExceptionMock->getCallCount());
    }

    public function testPrintExceptionPrintSeverity() {
        $errorException = new CM_Exception();
        $nativeException = new Exception();
        $fatalException = new CM_Exception(null, CM_Exception::FATAL);

        /** @var CM_ExceptionHandling_Handler_Abstract|\Mocka\AbstractClassTrait $exceptionHandler */
        $exceptionHandler = $this->mockClass('CM_ExceptionHandling_Handler_Abstract')->newInstanceWithoutConstructor();
        $exceptionHandler->mockMethod('logException')->set(function () {
        });

        $printExceptionMock = $exceptionHandler->mockMethod('_printException');
        $printExceptionMock
            ->at(0, function (Exception $ex) use ($nativeException) {
                $this->assertEquals($nativeException, $ex);
            })
            ->at(1, function (Exception $ex) use ($fatalException) {
                $this->assertEquals($fatalException, $ex);
            });

        $exceptionHandler->setPrintSeverityMin(CM_Exception::FATAL);

        /** @var CM_ExceptionHandling_Handler_Abstract $exceptionHandler */
        $exceptionHandler->handleException($errorException);
        $exceptionHandler->handleException($nativeException);
        $exceptionHandler->handleException($fatalException);

        $this->assertSame(2, $printExceptionMock->getCallCount());
    }
}
