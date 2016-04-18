<?php

class CM_Log_LoggerTest extends CMTest_TestCase {

    public function testConstructor() {
        /** @var CM_Log_Context $context */
        $context = $this->mockClass('CM_Log_Context')->newInstanceWithoutConstructor();
        $badHandlersLayerList = [
            ['foo', 'bar'],
        ];

        $exception = $this->catchException(function () use ($context) {
            new CM_Log_Logger($context, []);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Logger should have at least 1 handler layer', $exception->getMessage());

        $exception = $this->catchException(function () use ($context) {
            new CM_Log_Logger($context, [[$this->mockClass('CM_Log_Handler_Abstract')->newInstanceWithoutConstructor()], []]);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Empty handlers layer', $exception->getMessage());

        $exception = $this->catchException(function () use ($context, $badHandlersLayerList) {
            new CM_Log_Logger($context, $badHandlersLayerList);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Not logger handler instance', $exception->getMessage());

        $goodHandlersLayerList = [
            [
                $this->mockClass('CM_Log_Handler_Abstract')->newInstanceWithoutConstructor(),
                $this->mockClass('CM_Log_Handler_Abstract')->newInstanceWithoutConstructor(),
            ],
            [
                $this->mockClass('CM_Log_Handler_Abstract')->newInstanceWithoutConstructor(),
            ]
        ];
        $logger = new CM_Log_Logger($context, $goodHandlersLayerList);
        $this->assertInstanceOf('CM_Log_Logger', $logger);
    }

    public function testGetContext() {
        /** @var CM_Log_Context $context */
        $context = $this->mockClass('CM_Log_Context')->newInstanceWithoutConstructor();
        $logger = new CM_Log_Logger($context, [
            [$this->mockClass('CM_Log_Handler_Abstract')->newInstanceWithoutConstructor()],
        ]);
        $this->assertSame($context, $logger->getContext());
    }

    public function testAddRecord() {
        $mockLogHandler = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);
        $mockWriteRecord = $mockLogHandler->mockMethod('_writeRecord');

        $logger = $this->_getLoggerMock(new CM_Log_Context(), [[$mockLogHandler]]);

        $expectedRecord = new CM_Log_Record(CM_Log_Logger::ERROR, 'foo', new CM_Log_Context());
        $debugRecord = new CM_Log_Record(CM_Log_Logger::DEBUG, 'bar', new CM_Log_Context());
        $mockWriteRecord->set(function (CM_Log_Record $record) use ($expectedRecord) {
            $this->assertSame($expectedRecord, $record);
        });

        $this->callProtectedMethod($logger, '_addRecord', [$expectedRecord]);
        $this->callProtectedMethod($logger, '_addRecord', [$debugRecord]);
        $this->assertSame(1, $mockWriteRecord->getCallCount());
    }

    public function testHandlerLayerWriting() {
        $expectedRecord = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
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

        $logger = $this->_getLoggerMock(new CM_Log_Context(), [[$mockLogHandlerFoo, $mockLogHandlerBar, $mockLogHandlerBaz]]);

        $this->callProtectedMethod($logger, '_addRecord', [$expectedRecord]);
        $this->assertSame(1, $mockHandleRecordFoo->getCallCount());
        $this->assertSame(1, $mockHandleRecordBar->getCallCount());
        $this->assertSame(1, $mockHandleRecordBaz->getCallCount());
    }

    public function testHandlerLayerException() {
        $expectedRecord = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
        $mockLogHandlerFoo = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);
        $mockLogHandlerBar = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::WARNING]);
        $mockLogHandlerBaz = $this->mockClass('CM_Log_Handler_Abstract')->newInstance([CM_Log_Logger::ERROR]);

        $handleRecordOK = function (CM_Log_Record $record) use ($expectedRecord) {
            $this->assertSame($expectedRecord->getLevel(), $record->getLevel());
            $this->assertSame($expectedRecord->getMessage(), $record->getMessage());
        };

        $handleLoggerException = function (CM_Log_Record $record) {
            $this->assertInstanceOf('CM_Log_Record_Exception', $record);
            /** @var CM_Log_Record_Exception $record */
            $originalException = $record->getException();
            $this->assertInstanceOf('CM_Log_HandlingException', $originalException);
            $this->assertSame('Handler error', $originalException->getMessage());
        };

        $handleRecordFail = function () {
            throw new CM_Exception_Invalid('Handler error');
        };

        $mockHandleRecordFoo = $mockLogHandlerFoo->mockMethod('handleRecord')
            ->at(0, $handleRecordOK)
            ->at(1, $handleLoggerException);
        $mockHandleRecordBar = $mockLogHandlerBar->mockMethod('handleRecord')
            ->at(0, $handleRecordFail)
            ->at(1, $handleLoggerException);
        $mockHandleRecordBaz = $mockLogHandlerBaz->mockMethod('handleRecord')
            ->at(0, $handleRecordOK)
            ->at(1, $handleLoggerException);

        $logger = $this->_getLoggerMock(new CM_Log_Context(), [[$mockLogHandlerFoo, $mockLogHandlerBar, $mockLogHandlerBaz]]);

        $this->callProtectedMethod($logger, '_addRecord', [$expectedRecord]);
        $this->assertSame(2, $mockHandleRecordFoo->getCallCount());
        $this->assertSame(2, $mockHandleRecordBar->getCallCount());
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
                $this->assertInstanceOf('CM_Log_Record_Exception', $record);
                /** @var CM_Log_Record_Exception $record */
                $exception = $record->getException();
                $this->assertInstanceOf('CM_Log_HandlingException', $exception);
                $this->assertSame($messageToAssert, $exception->getMessage());

                if (null !== $messageToThrow) {
                    throw new Exception($messageToThrow);
                }
                return true;
            };
        };

        $mockHandleRecordFoo = $mockLogHandlerFoo->mockMethod('handleRecord')
            ->at(0, function () {
                throw new CM_Exception_Invalid('Foo Error');
            })
            ->at(1, $assertHandlerException('Baz Error'))
            ->at(2, $assertHandlerException('Baz2 Error'))
            ->at(3, $assertHandlerException('Foo Error', 'Foo Error2'))
            ->at(4, $assertHandlerException('Bar Error', 'Foo Error3'))
            ->at(5, $assertHandlerException('FooBar Error'));

        $mockHandleRecordBar = $mockLogHandlerBar->mockMethod('handleRecord')
            ->at(0, function () {
                throw new CM_Exception_Invalid('Bar Error');
            })
            ->at(1, $assertHandlerException('Baz Error'))
            ->at(2, $assertHandlerException('Baz2 Error'))
            ->at(3, $assertHandlerException('Foo Error'))
            ->at(4, $assertHandlerException('Bar Error', 'Bar Error2'))
            ->at(5, $assertHandlerException('FooBar Error'));

        $mockHandleRecordFooBar = $mockLogHandlerFooBar->mockMethod('handleRecord')
            ->at(0, function () {
                throw new CM_Exception_Invalid('FooBar Error');
            })
            ->at(1, $assertHandlerException('Baz Error'))
            ->at(2, $assertHandlerException('Baz2 Error'))
            ->at(3, $assertHandlerException('Foo Error'))
            ->at(4, $assertHandlerException('Bar Error', 'FooBar Error2'))
            ->at(5, $assertHandlerException('FooBar Error'));

        $mockHandleRecordBaz = $mockLogHandlerBaz->mockMethod('handleRecord')
            ->at(0, function (CM_Log_Record $record) use ($expectedRecord) {
                $this->assertSame($expectedRecord->getLevel(), $record->getLevel());
                $this->assertSame($expectedRecord->getMessage(), $record->getMessage());
                throw new Exception('Baz Error');
            })
            ->at(1, $assertHandlerException('Bar Error'));

        $mockHandleRecordBaz2 = $mockLogHandlerBaz2->mockMethod('handleRecord')
            ->at(0, function (CM_Log_Record $record) use ($expectedRecord) {
                $this->assertSame($expectedRecord->getLevel(), $record->getLevel());
                $this->assertSame($expectedRecord->getMessage(), $record->getMessage());
                throw new Exception('Baz2 Error');
            })
            ->at(1, $assertHandlerException('Bar Error'));

        $mockHandleRecordQuux = $mockLogHandlerQuux->mockMethod('handleRecord')
            ->at(0, function (CM_Log_Record $record) use ($expectedRecord) {
                $this->assertSame($expectedRecord->getLevel(), $record->getLevel());
                $this->assertSame($expectedRecord->getMessage(), $record->getMessage());
            });

        $logger = $this->_getLoggerMock(new CM_Log_Context(), [
            [$mockLogHandlerFoo, $mockLogHandlerBar, $mockLogHandlerFooBar],
            [$mockLogHandlerBaz, $mockLogHandlerBaz2],
            [$mockLogHandlerQuux]
        ]);

        $this->callProtectedMethod($logger, '_addRecord', [$expectedRecord]);
        $this->assertSame(6, $mockHandleRecordFoo->getCallCount());
        $this->assertSame(6, $mockHandleRecordBar->getCallCount());
        $this->assertSame(6, $mockHandleRecordFooBar->getCallCount());

        $this->assertSame(2, $mockHandleRecordBaz->getCallCount());
        $this->assertSame(2, $mockHandleRecordBaz2->getCallCount());
        $this->assertSame(1, $mockHandleRecordQuux->getCallCount());
    }

    public function testLoggingWithContext() {
        $mockLogHandler = $this->mockInterface('CM_Log_Handler_HandlerInterface')->newInstance();
        $mockHandleRecord = $mockLogHandler->mockMethod('handleRecord');

        // without any context
        $logger = $this->_getLoggerMock(new CM_Log_Context(), [[$mockLogHandler]]);
        $mockHandleRecord->set(function (CM_Log_Record $record) {
            $context = $record->getContext();
            $this->assertNull($context->getComputerInfo());
            $this->assertNull($context->getUser());
            $this->assertNull($context->getHttpRequest());
            $this->assertSame([], $context->getExtra());
        });
        $logger->addMessage('foo', CM_Log_Logger::INFO);
        $this->assertSame(1, $mockHandleRecord->getCallCount());

        // with a global context
        $computerInfo = new CM_Log_Context_ComputerInfo('foo.dev', '42.0');
        $contextGlobal = new CM_Log_Context(null, $computerInfo);
        $logger = $this->_getLoggerMock($contextGlobal, [[$mockLogHandler]]);

        $mockHandleRecord->set(function (CM_Log_Record $record) use ($computerInfo) {
            $context = $record->getContext();
            $this->assertEquals($computerInfo, $context->getComputerInfo());
            $this->assertNull($context->getUser());
            $this->assertNull($context->getHttpRequest());
            $this->assertSame([], $context->getExtra());
        });
        $logger->addMessage('foo', CM_Log_Logger::INFO);
        $this->assertSame(2, $mockHandleRecord->getCallCount());

        // with a global context + log context
        $computerInfo = new CM_Log_Context_ComputerInfo('foo.dev', '42.0');
        $contextGlobal = new CM_Log_Context(null, $computerInfo);
        $logger = $this->_getLoggerMock($contextGlobal, [[$mockLogHandler]]);

        $mockHandleRecord->set(function (CM_Log_Record $record) use ($computerInfo) {
            $context = $record->getContext();
            $this->assertEquals($computerInfo, $context->getComputerInfo());
            $this->assertNull($context->getUser());
            $this->assertNull($context->getHttpRequest());
            $this->assertSame(['foo' => 10], $context->getExtra());
        });
        $logger->addMessage('foo', CM_Log_Logger::INFO, new CM_Log_Context_App(['foo' => 10]));
        $this->assertSame(3, $mockHandleRecord->getCallCount());
    }

    public function testLogHelpers() {
        $mockLogHandler = $this->mockInterface('CM_Log_Handler_HandlerInterface')->newInstance();
        $mockHandleRecord = $mockLogHandler->mockMethod('handleRecord');

        $logger = $this->_getLoggerMock(new CM_Log_Context(), [[$mockLogHandler]]);

        $mockHandleRecord->set(function (CM_Log_Record $record) {
            $this->assertSame('message sent using debug method', $record->getMessage());
            $this->assertSame(CM_Log_Logger::DEBUG, $record->getLevel());
        });
        $logger->debug('message sent using debug method');
        $this->assertSame(1, $mockHandleRecord->getCallCount());

        $mockHandleRecord->set(function (CM_Log_Record $record) {
            $this->assertSame('message sent using info method', $record->getMessage());
            $this->assertSame(CM_Log_Logger::INFO, $record->getLevel());
        });
        $logger->info('message sent using info method');
        $this->assertSame(2, $mockHandleRecord->getCallCount());

        $mockHandleRecord->set(function (CM_Log_Record $record) {
            $this->assertSame('message sent using warning method', $record->getMessage());
            $this->assertSame(CM_Log_Logger::WARNING, $record->getLevel());
        });
        $logger->warning('message sent using warning method');
        $this->assertSame(3, $mockHandleRecord->getCallCount());

        $mockHandleRecord->set(function (CM_Log_Record $record) {
            $this->assertSame('message sent using error method', $record->getMessage());
            $this->assertSame(CM_Log_Logger::ERROR, $record->getLevel());
        });
        $logger->error('message sent using error method');
        $this->assertSame(4, $mockHandleRecord->getCallCount());

        $mockHandleRecord->set(function (CM_Log_Record $record) {
            $this->assertSame('message sent using critical method', $record->getMessage());
            $this->assertSame(CM_Log_Logger::CRITICAL, $record->getLevel());
        });
        $logger->critical('message sent using critical method');
        $this->assertSame(5, $mockHandleRecord->getCallCount());
    }

    public function testHandleException() {
        $mockLogHandler = $this->mockInterface('CM_Log_Handler_HandlerInterface')->newInstance();
        $mockHandleRecord = $mockLogHandler->mockMethod('handleRecord');

        $logger = $this->_getLoggerMock(new CM_Log_Context(), [[$mockLogHandler]]);

        $exception = new Exception('foo');
        $mockHandleRecord->set(function (CM_Log_Record_Exception $record) use ($exception) {
            $recordException = $record->getSerializableException();
            $this->assertSame($exception->getMessage(), $recordException->getMessage());
            $this->assertSame($exception->getLine(), $recordException->getLine());
            $this->assertSame($exception->getFile(), $recordException->getFile());
            $this->assertSame('Exception: foo', $record->getMessage());
            $this->assertSame(CM_Log_Logger::ERROR, $record->getLevel());
        });
        $logger->addException($exception, CM_Log_Logger::ERROR);
        $this->assertSame(1, $mockHandleRecord->getCallCount());

        $exception = new CM_Exception('bar');
        $mockHandleRecord->set(function (CM_Log_Record_Exception $record) use ($exception) {
            $recordException = $record->getSerializableException();
            $this->assertSame($exception->getMessage(), $recordException->getMessage());
            $this->assertSame($exception->getLine(), $recordException->getLine());
            $this->assertSame($exception->getFile(), $recordException->getFile());
            $this->assertSame('CM_Exception: bar', $record->getMessage());
            $this->assertSame(CM_Log_Logger::WARNING, $record->getLevel());
        });
        $logger->addException($exception, CM_Log_Logger::WARNING);
        $this->assertSame(2, $mockHandleRecord->getCallCount());

        $exception = new CM_Exception('foobar');
        $mockHandleRecord->set(function (CM_Log_Record_Exception $record) use ($exception) {
            $recordException = $record->getSerializableException();
            $this->assertSame($exception->getMessage(), $recordException->getMessage());
            $this->assertSame($exception->getLine(), $recordException->getLine());
            $this->assertSame($exception->getFile(), $recordException->getFile());
            $this->assertSame('CM_Exception: foobar', $record->getMessage());
            $this->assertSame(CM_Log_Logger::ERROR, $record->getLevel());
        });
        $logger->addException($exception, CM_Log_Logger::ERROR);
        $this->assertSame(3, $mockHandleRecord->getCallCount());

        $exception = new CM_Exception('test');
        $mockHandleRecord->set(function (CM_Log_Record_Exception $record) use ($exception) {
            $recordException = $record->getSerializableException();
            $this->assertSame($exception->getMessage(), $recordException->getMessage());
            $this->assertSame($exception->getLine(), $recordException->getLine());
            $this->assertSame($exception->getFile(), $recordException->getFile());
            $this->assertSame('CM_Exception: test', $record->getMessage());
            $this->assertSame(CM_Log_Logger::CRITICAL, $record->getLevel());
        });
        $logger->addException($exception, CM_Log_Logger::CRITICAL);
        $this->assertSame(4, $mockHandleRecord->getCallCount());
    }

    public function testStaticLogLevelMethods() {
        $this->assertSame('INFO', CM_Log_Logger::getLevelName(CM_Log_Logger::INFO));
        $this->assertNotEmpty(CM_Log_Logger::getLevels());
        $this->assertTrue(CM_Log_Logger::hasLevel(CM_Log_Logger::INFO));
        $this->assertFalse(CM_Log_Logger::hasLevel(666));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage is not defined, use one of
     */
    public function testStaticGetLevelNameException() {
        CM_Log_Logger::getLevelName(666);
    }

    public function testLoopIsNotEndless() {
        $expectedRecord = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());

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

        $logger = $this->_getLoggerMock(new CM_Log_Context(), [
            [$mockLogHandlerFoo, $mockLogHandlerBar, $mockLogHandlerFooBar],
            [$mockLogHandlerBaz, $mockLogHandlerBaz2],
            [$mockLogHandlerQuux, $mockLogHandlerQuux2],
        ]);

        $this->callProtectedMethod($logger, '_addRecord', [$expectedRecord]);

        $this->assertSame(8, $mockHandleRecordFoo->getCallCount());
        $this->assertSame(8, $mockHandleRecordBar->getCallCount());
        $this->assertSame(8, $mockHandleRecordFooBar->getCallCount());
        $this->assertSame(8, $mockHandleRecordBaz->getCallCount());
        $this->assertSame(8, $mockHandleRecordBaz2->getCallCount());
        $this->assertSame(8, $mockHandleRecordQuux->getCallCount());
        $this->assertSame(8, $mockHandleRecordQuux2->getCallCount());
    }

    /**
     * @param CM_Log_Context $context
     * @param array          $handlersLayerList
     * @return CM_Log_Logger|\Mocka\AbstractClassTrait
     */
    protected function _getLoggerMock(CM_Log_Context $context, array $handlersLayerList) {
        $loggerMock = $this->mockClass('CM_Log_Logger')->newInstance([$context, $handlersLayerList]);
        $logExceptionsMock = $loggerMock->mockMethod('_logHandlersExceptions');
        $logExceptionsMock->set(function (CM_Log_Record $record, array $exceptionList, CM_Log_Context $context) use ($loggerMock) {
            if ($exception = \Functional\first($exceptionList, function (CM_Log_HandlingException $e) {
                return $e->getOriginalException() instanceof PHPUnit_Framework_Exception;
            })) {
                throw $exception;
            }
            $loggerMock->callOriginalMethod('_logHandlersExceptions', func_get_args());
        });
        return $loggerMock;
    }
}
