<?php

class CM_ExceptionHandling_Handler_AbstractTest extends CMTest_TestCase {

    public function testHandleException() {
        $expectedException = new Exception('foo');

        /** @var CM_Log_Factory|\Mocka\ClassMock $loggerFactory */
        $loggerFactory = $this->mockClass('CM_Log_Factory')->newInstance();

        /** @var CM_Log_Logger|\Mocka\ClassMock $logger */
        $logger = $this->mockClass('CM_Log_Logger')->newInstance([new CM_Log_Context()]);
        $methodAddException = $logger->mockMethod('addException');
        $methodAddException->set(
            function (Exception $exception) use ($expectedException) {
                $this->assertEquals($expectedException, $exception);
            }
        );

        /** @var CM_Service_Manager|\Mocka\ClassMock $serviceManager */
        $serviceManager = $this->mockClass('CM_Service_Manager')->newInstance();
        $serviceManager->mockMethod('getLogger')->set($logger);

        /** @var CM_ExceptionHandling_Handler_Abstract $exceptionHandler */
        $exceptionHandler = $this->mockClass('CM_ExceptionHandling_Handler_Abstract')->newInstance([$loggerFactory]);
        $exceptionHandler->setServiceManager($serviceManager);
        $exceptionHandler->handleException($expectedException);

        $this->assertSame(1, $methodAddException->getCallCount());
    }

    public function testHandleExceptionWithLoggerError() {
        $expectedException = new Exception('foo');

        /** @var CM_Log_Factory|\Mocka\ClassMock $loggerFactory */
        $loggerFactory = $this->mockClass('CM_Log_Factory')->newInstance();

        /** @var CM_Log_Logger|\Mocka\ClassMock $logger */
        $logger = $this->mockClass('CM_Log_Logger')->newInstance([new CM_Log_Context()]);

        /** @var CM_Log_Logger|\Mocka\ClassMock $backupLogger */
        $backupLogger = $this->mockClass('CM_Log_Logger')->newInstance([new CM_Log_Context()]);

        /** @var \Mocka\FunctionMock $methodAddException */
        $methodAddException = $logger->mockMethod('addException');
        $methodAddException->set(function () {
            throw new Exception('Logger failed.');
        });

        /** @var \Mocka\FunctionMock $backupMethodAddException */
        $backupMethodAddException = $backupLogger->mockMethod('addException');
        $backupMethodAddException
            ->at(0, function (Exception $exception) use ($backupLogger, $expectedException) {
                $this->assertEquals($expectedException, $exception);
                return $backupLogger;
            })
            ->at(1, function (Exception $exception) use ($backupLogger) {
                $this->assertSame('Logger failed.', $exception->getMessage());
                return $backupLogger;
            });

        /** @var \Mocka\FunctionMock $methodError */
        $methodError = $backupLogger->mockMethod('error');
        $methodError
            ->at(0, function ($message) use ($backupLogger) {
                $this->assertSame('Origin exception:', $message);
                return $backupLogger;
            })
            ->at(1, function ($message) use ($backupLogger) {
                $this->assertSame('Logger exception:', $message);
                return $backupLogger;
            });

        /** @var CM_Service_Manager|\Mocka\ClassMock $serviceManager */
        $serviceManager = $this->mockClass('CM_Service_Manager')->newInstance();
        $serviceManager->mockMethod('getLogger')->set($logger);

        /** @var CM_ExceptionHandling_Handler_Abstract|\Mocka\ClassMock $exceptionHandlerMock */
        $exceptionHandlerMock = $this->mockClass('CM_ExceptionHandling_Handler_Abstract')->newInstance([$loggerFactory]);
        $exceptionHandlerMock->mockMethod('_getBackupLogger')->set($backupLogger);

        $exceptionHandlerMock->setServiceManager($serviceManager);
        $exceptionHandlerMock->handleException($expectedException);

        $this->assertSame(1, $methodAddException->getCallCount());
        $this->assertSame(2, $backupMethodAddException->getCallCount());
        $this->assertSame(2, $methodError->getCallCount());
    }

    public function testHandleExceptionWithHandingException() {
        $expectedException = new Exception('foo');

        /** @var CM_Log_Logger|\Mocka\ClassMock $backupLogger */
        $backupLogger = $this->mockClass('CM_Log_Logger')->newInstance([new CM_Log_Context()]);

        /** @var CM_Log_Factory|\Mocka\ClassMock $loggerFactory */
        $loggerFactory = $this->mockClass('CM_Log_Factory')->newInstance();

        /** @var \Mocka\FunctionMock $methodAddException */
        $methodAddException = $backupLogger->mockMethod('addException');
        $methodAddException
            ->at(0, function (Exception $exception) use ($backupLogger) {
                $this->assertSame('foo', $exception->getMessage());
                return $backupLogger;
            })
            ->at(1, function (Exception $exception) use ($backupLogger) {
                $this->assertSame('handler exception message.', $exception->getMessage());
                return $backupLogger;
            });

        /** @var \Mocka\FunctionMock $methodError */
        $methodError = $backupLogger->mockMethod('error');
        $methodError
            ->at(0, function ($message) use ($backupLogger) {
                $this->assertSame('Origin exception:', $message);
                return $backupLogger;
            })
            ->at(1, function ($message) use ($backupLogger) {
                $this->assertSame('Handlers exception:', $message);
                return $backupLogger;
            })
            ->at(2, function ($message) use ($backupLogger) {
                $this->assertSame('1 handler(s) failed to process a record.', $message);
                return $backupLogger;
            });

        /** @var CM_Log_Handler_HandlerInterface|\Mocka\ClassMock $handler */
        $handler = $this->mockInterface('CM_Log_Handler_HandlerInterface')->newInstance();
        /** @var \Mocka\FunctionMock $methodHandleRecord */
        $methodHandleRecord = $handler->mockMethod('handleRecord')
            ->set(function () {
                throw new Exception('handler exception message.');
            });
        /** @var CM_Log_Logger|\Mocka\ClassMock $logger */
        $logger = $this->mockClass('CM_Log_Logger')->newInstance([new CM_Log_Context(), [$handler]]);

        /** @var CM_Service_Manager|\Mocka\ClassMock $serviceManager */
        $serviceManager = $this->mockClass('CM_Service_Manager')->newInstance();
        $serviceManager->mockMethod('getLogger')->set($logger);

        /** @var CM_ExceptionHandling_Handler_Abstract|\Mocka\ClassMock $exceptionHandlerMock */
        $exceptionHandlerMock = $this->mockClass('CM_ExceptionHandling_Handler_Abstract')->newInstance([$loggerFactory]);
        $exceptionHandlerMock->mockMethod('_getBackupLogger')->set($backupLogger);
        $exceptionHandlerMock->setServiceManager($serviceManager);

        $exceptionHandlerMock->handleException($expectedException);

        $this->assertSame(2, $methodAddException->getCallCount());
        $this->assertSame(3, $methodError->getCallCount());
        $this->assertSame(1, $methodHandleRecord->getCallCount());
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
