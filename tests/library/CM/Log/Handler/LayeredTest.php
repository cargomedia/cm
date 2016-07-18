<?php

class CM_Log_Handler_LayeredTest extends CMTest_TestCase {

    public function testHandlerLayerWriting() {
        $expectedRecord = new CM_Log_Record(CM_Log_Logger::ERROR, 'foo', new CM_Log_Context());
        $mockLogHandlerFoo = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);
        $mockLogHandlerBar = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::WARNING]);
        $mockLogHandlerBaz = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);

        $handleRecord = function (CM_Log_Record $record) use ($expectedRecord) {
            $this->assertSame($expectedRecord->getLevel(), $record->getLevel());
            $this->assertSame($expectedRecord->getMessage(), $record->getMessage());
        };

        $mockHandleRecordFoo = $mockLogHandlerFoo->mockMethod('handleRecord')->set($handleRecord);
        $mockHandleRecordBar = $mockLogHandlerBar->mockMethod('handleRecord')->set($handleRecord);
        $mockHandleRecordBaz = $mockLogHandlerBaz->mockMethod('handleRecord')->set($handleRecord);

        $logger = $this->_getLoggerMock(
            new CM_Log_Context(),
            new CM_Log_Handler_Layered([
                new CM_Log_Handler_Layered_Layer([$mockLogHandlerFoo, $mockLogHandlerBar, $mockLogHandlerBaz])
            ])
        );

        $this->callProtectedMethod($logger, '_addRecord', [$expectedRecord]);
        $this->assertSame(1, $mockHandleRecordFoo->getCallCount());
        $this->assertSame(1, $mockHandleRecordBar->getCallCount());
        $this->assertSame(1, $mockHandleRecordBaz->getCallCount());
    }

    public function testHandlerLayerException() {
        $expectedRecord = new CM_Log_Record(CM_Log_Logger::ERROR, 'foo', new CM_Log_Context());
        $mockLogHandlerFoo = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);
        $mockLogHandlerBar = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::WARNING]);
        $mockLogHandlerBaz = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);

        $assertHandleRecordOK = function (CM_Log_Record $record) use ($expectedRecord) {
            $this->assertSame($expectedRecord->getLevel(), $record->getLevel());
            $this->assertSame($expectedRecord->getMessage(), $record->getMessage());
        };

        $assertHandleLoggerException = function (CM_Log_Record $record) {
            $originalException = $record->getContext()->getException();
            $this->assertInstanceOf('Exception', $originalException);
            $this->assertSame('Handler error', $originalException->getMessage());
        };

        $handleRecordFail = function () {
            throw new CM_Exception_Invalid('Handler error');
        };

        $mockHandleRecordFoo = $mockLogHandlerFoo->mockMethod('handleRecord')
            ->at(0, $assertHandleRecordOK)
            ->at(1, $assertHandleLoggerException);
        $mockHandleRecordBar = $mockLogHandlerBar->mockMethod('handleRecord')
            ->at(0, $handleRecordFail);
        $mockHandleRecordBaz = $mockLogHandlerBaz->mockMethod('handleRecord')
            ->at(0, $assertHandleRecordOK)
            ->at(1, $assertHandleLoggerException);

        $logger = $this->_getLoggerMock(
            new CM_Log_Context(),
            new CM_Log_Handler_Layered([
                new CM_Log_Handler_Layered_Layer([$mockLogHandlerFoo, $mockLogHandlerBar, $mockLogHandlerBaz])
            ])
        );

        $this->callProtectedMethod($logger, '_addRecord', [$expectedRecord]);
        $this->assertSame(2, $mockHandleRecordFoo->getCallCount());
        $this->assertSame(1, $mockHandleRecordBar->getCallCount());
        $this->assertSame(2, $mockHandleRecordBaz->getCallCount());
    }

    public function testPassingMessageDown() {
        $expectedRecord = new CM_Log_Record(CM_Log_Logger::CRITICAL, 'foo', new CM_Log_Context());
        $mockLogHandlerFoo = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);
        $mockLogHandlerBar = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::WARNING]);
        $mockLogHandlerFooBar = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::INFO]);

        $mockLogHandlerBaz = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);
        $mockLogHandlerBaz2 = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);

        $mockLogHandlerQuux = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);

        $assertHandlerException = function ($messageToAssert, $messageToThrow = null) {
            return function (CM_Log_Record $record) use ($messageToAssert, $messageToThrow) {
                $exception = $record->getContext()->getException();
                $this->assertInstanceOf('Exception', $exception);
                $this->assertSame($messageToAssert, $exception->getMessage());

                if (null !== $messageToThrow) {
                    throw new Exception($messageToThrow);
                }
            };
        };

        $mockHandleRecordFoo = $mockLogHandlerFoo->mockMethod('handleRecord')
            ->at(0, function () {
                throw new CM_Exception_Invalid('Foo Error');
            });

        $mockHandleRecordBar = $mockLogHandlerBar->mockMethod('handleRecord')
            ->at(0, function () {
                throw new CM_Exception_Invalid('Bar Error');
            });

        $mockHandleRecordFooBar = $mockLogHandlerFooBar->mockMethod('handleRecord')
            ->at(0, function () {
                throw new CM_Exception_Invalid('FooBar Error');
            });

        $mockHandleRecordBaz = $mockLogHandlerBaz->mockMethod('handleRecord')
            ->at(0, function (CM_Log_Record $record) use ($expectedRecord) {
                $this->assertSame($expectedRecord->getLevel(), $record->getLevel());
                $this->assertSame($expectedRecord->getMessage(), $record->getMessage());
                throw new Exception('Baz Error');
            })
            ->at(1, $assertHandlerException('Foo Error', 'Baz Error2'))
            ->at(2, $assertHandlerException('Bar Error'))
            ->at(3, $assertHandlerException('FooBar Error'));

        $mockHandleRecordBaz2 = $mockLogHandlerBaz2->mockMethod('handleRecord')
            ->at(0, function (CM_Log_Record $record) use ($expectedRecord) {
                $this->assertSame($expectedRecord->getLevel(), $record->getLevel());
                $this->assertSame($expectedRecord->getMessage(), $record->getMessage());
                throw new Exception('Baz2 Error');
            })
            ->at(1, $assertHandlerException('Foo Error'))
            ->at(2, $assertHandlerException('Baz Error2'))
            ->at(3, $assertHandlerException('Bar Error'))
            ->at(4, $assertHandlerException('FooBar Error'));

        $mockHandleRecordQuux = $mockLogHandlerQuux->mockMethod('handleRecord')
            ->at(0, function (CM_Log_Record $record) use ($expectedRecord) {
                $this->assertSame($expectedRecord->getLevel(), $record->getLevel());
                $this->assertSame($expectedRecord->getMessage(), $record->getMessage());
            })
            ->at(1, $assertHandlerException('Baz Error'))
            ->at(2, $assertHandlerException('Baz2 Error'));

        $logger = $this->_getLoggerMock(
            new CM_Log_Context(),
            new CM_Log_Handler_Layered([
                new CM_Log_Handler_Layered_Layer([$mockLogHandlerFoo, $mockLogHandlerBar, $mockLogHandlerFooBar]),
                new CM_Log_Handler_Layered_Layer([$mockLogHandlerBaz, $mockLogHandlerBaz2]),
                new CM_Log_Handler_Layered_Layer([$mockLogHandlerQuux]),
            ])
        );

        $this->callProtectedMethod($logger, '_addRecord', [$expectedRecord]);
        $this->assertSame(1, $mockHandleRecordFoo->getCallCount());
        $this->assertSame(1, $mockHandleRecordBar->getCallCount());
        $this->assertSame(1, $mockHandleRecordFooBar->getCallCount());

        $this->assertSame(4, $mockHandleRecordBaz->getCallCount());
        $this->assertSame(5, $mockHandleRecordBaz2->getCallCount());
        $this->assertSame(3, $mockHandleRecordQuux->getCallCount());
    }

    public function testPassingMessageDownWithSkipping() {
        $expectedRecord = new CM_Log_Record(CM_Log_Logger::WARNING, 'foo', new CM_Log_Context());
        $mockLogHandlerFoo = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);
        $mockLogHandlerBar = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::WARNING]);
        $mockLogHandlerBaz = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::WARNING]);

        $assertHandlerException = function ($messageToAssert, $messageToThrow = null) {
            return function (CM_Log_Record $record) use ($messageToAssert, $messageToThrow) {
                $exception = $record->getContext()->getException();
                $this->assertInstanceOf('Exception', $exception);
                $this->assertSame($messageToAssert, $exception->getMessage());

                if (null !== $messageToThrow) {
                    throw new Exception($messageToThrow);
                }
            };
        };

        $mockWriteRecordFoo = $mockLogHandlerFoo->mockMethod('_writeRecord');

        $mockWriteRecordBar = $mockLogHandlerBar->mockMethod('_writeRecord')
            ->at(0, function (CM_Log_Record $record) use ($expectedRecord) {
                $this->assertSame($expectedRecord->getLevel(), $record->getLevel());
                $this->assertSame($expectedRecord->getMessage(), $record->getMessage());
                throw new CM_Exception_Invalid('Foo Error');
            });

        $mockWriteRecordBaz = $mockLogHandlerBaz->mockMethod('_writeRecord')
            ->at(0, function (CM_Log_Record $record) use ($expectedRecord) {
                $this->assertSame($expectedRecord->getLevel(), $record->getLevel());
                $this->assertSame($expectedRecord->getMessage(), $record->getMessage());
            })
            ->at(1, $assertHandlerException('Foo Error'));

        $logger = $this->_getLoggerMock(
            new CM_Log_Context(),
            new CM_Log_Handler_Layered([
                new CM_Log_Handler_Layered_Layer([$mockLogHandlerFoo, $mockLogHandlerBar]),
                new CM_Log_Handler_Layered_Layer([$mockLogHandlerBaz]),
            ])
        );

        $this->callProtectedMethod($logger, '_addRecord', [$expectedRecord]);
        $this->assertSame(0, $mockWriteRecordFoo->getCallCount());
        //foo will skip WARN record
        $this->assertSame(1, $mockWriteRecordBar->getCallCount());
        //bar will just fail
        $this->assertSame(2, $mockWriteRecordBaz->getCallCount());
        //original record + foo error msg
    }

    public function testHandleException() {
        $mockLogHandler = $this->mockInterface('CM_Log_Handler_HandlerInterface')->newInstance();
        $mockHandleRecord = $mockLogHandler->mockMethod('handleRecord');

        $logger = $this->_getLoggerMock(
            new CM_Log_Context(),
            new CM_Log_Handler_Layered([
                new CM_Log_Handler_Layered_Layer([$mockLogHandler])
            ])
        );

        $exception = new Exception('foo');
        $mockHandleRecord->set(function (CM_Log_Record $record) use ($exception) {
            $this->assertTrue(!!$record->getContext()->getException());
            $recordException = $record->getContext()->getException();
            $this->assertSame('foo', $recordException->getMessage());
            $this->assertSame($exception->getLine(), $recordException->getLine());
            $this->assertSame($exception->getFile(), $recordException->getFile());
            $this->assertSame('Error happened', $record->getMessage());
            $this->assertSame(CM_Log_Logger::ERROR, $record->getLevel());
        });
        $context = new CM_Log_Context();
        $context->setException($exception);
        $logger->error('Error happened', $context);
        $this->assertSame(1, $mockHandleRecord->getCallCount());

        $exception = new CM_Exception('bar');
        $mockHandleRecord->set(function (CM_Log_Record $record) use ($exception) {
            $recordException = $record->getContext()->getException();
            $this->assertSame('bar', $recordException->getMessage());
            $this->assertSame($exception->getLine(), $recordException->getLine());
            $this->assertSame($exception->getFile(), $recordException->getFile());
            $this->assertSame('Warning alert', $record->getMessage());
            $this->assertSame(CM_Log_Logger::WARNING, $record->getLevel());
        });
        $context = new CM_Log_Context();
        $context->setException($exception);
        $logger->warning('Warning alert', $context);
        $this->assertSame(2, $mockHandleRecord->getCallCount());
    }

    public function testLoopIsNotEndless() {
        $expectedRecord = new CM_Log_Record(CM_Log_Logger::CRITICAL, 'foo', new CM_Log_Context());

        $mockLogHandlerFoo = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);
        $mockLogHandlerBar = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::WARNING]);
        $mockLogHandlerFooBar = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::INFO]);

        $mockLogHandlerBaz = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);
        $mockLogHandlerBaz2 = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);

        $mockLogHandlerQuux = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);
        $mockLogHandlerQuux2 = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);

        $mockHandleRecordFoo = $mockLogHandlerFoo->mockMethod('handleRecord')
            ->set(function () {
                throw new CM_Exception_Invalid('Foo Error');
            });
        $mockHandleRecordBar = $mockLogHandlerBar->mockMethod('handleRecord')
            ->set(function () {
                throw new CM_Exception_Invalid('Bar Error');
            });
        $mockHandleRecordFooBar = $mockLogHandlerFooBar->mockMethod('handleRecord')
            ->set(function () {
                throw new CM_Exception_Invalid('FooBar Error');
            });
        $mockHandleRecordBaz = $mockLogHandlerBaz->mockMethod('handleRecord')
            ->set(function () {
                throw new CM_Exception_Invalid('Baz Error');
            });
        $mockHandleRecordBaz2 = $mockLogHandlerBaz2->mockMethod('handleRecord')
            ->set(function () {
                throw new CM_Exception_Invalid('Baz2 Error');
            });
        $mockHandleRecordQuux = $mockLogHandlerQuux->mockMethod('handleRecord')
            ->set(function () {
                throw new CM_Exception_Invalid('Quux Error');
            });
        $mockHandleRecordQuux2 = $mockLogHandlerQuux2->mockMethod('handleRecord')
            ->set(function () {
                throw new CM_Exception_Invalid('Quux2 Error');
            });

        $logger = $this->_getLoggerMock(
            new CM_Log_Context(),
            new CM_Log_Handler_Layered([
                new CM_Log_Handler_Layered_Layer([$mockLogHandlerFoo, $mockLogHandlerBar, $mockLogHandlerFooBar]),
                new CM_Log_Handler_Layered_Layer([$mockLogHandlerBaz, $mockLogHandlerBaz2]),
                new CM_Log_Handler_Layered_Layer([$mockLogHandlerQuux, $mockLogHandlerQuux2]),
            ])
        );

        $this->callProtectedMethod($logger, '_addRecord', [$expectedRecord]);

        $this->assertSame(1, $mockHandleRecordFoo->getCallCount());
        $this->assertSame(1, $mockHandleRecordBar->getCallCount());
        $this->assertSame(1, $mockHandleRecordFooBar->getCallCount());
        //first layer fails completely, their errors goes to the second
        $this->assertSame(4, $mockHandleRecordBaz->getCallCount());
        $this->assertSame(4, $mockHandleRecordBaz2->getCallCount());
        //original message + 1 error per each handler of 1st layer
        $this->assertSame(12, $mockHandleRecordQuux->getCallCount());
        $this->assertSame(12, $mockHandleRecordQuux2->getCallCount());
        //original message + 1 error per each handler of 1st layer
        // + 4 (2nd level handling errors) * 2 (2nd handlers) == 1 + 3 + 8
    }

    /**
     * @param CM_Log_Context         $context
     * @param CM_Log_Handler_Layered $layered
     * @return CM_Log_Logger|\Mocka\AbstractClassTrait
     * @throws \Mocka\Exception
     */
    protected function _getLoggerMock(CM_Log_Context $context, CM_Log_Handler_Layered $layered) {
        $loggerMock = $this->mockClass('CM_Log_Logger')->newInstance([$context, $layered]);
        $logExceptionsMock = $loggerMock->mockMethod('_logHandlersExceptions');
        $logExceptionsMock->set(function (CM_Log_Record $record, array $exceptionList, CM_Log_Context $context) use ($loggerMock) {
            if ($exception = \Functional\first($exceptionList, function (CM_Log_HandlingException $e) {
                return $e->getOriginalException() instanceof PHPUnit_Framework_Exception;
            })
            ) {
                throw $exception;
            }
            $loggerMock->callOriginalMethod('_logHandlersExceptions', func_get_args());
        });
        return $loggerMock;
    }

    public function testStaticGetLevelNameException() {
        $exception = $this->catchException(function () {
            CM_Log_Logger::getLevelName(666);
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Level is not defined', $exception->getMessage());
        $this->assertSame(
            [
                'level'           => 666,
                'availableLevels' => '100, 200, 300, 400, 500',
            ],
            $exception->getMetaInfo()
        );
    }
}
