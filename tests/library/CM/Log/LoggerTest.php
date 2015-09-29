<?php

class CM_Log_LoggerTest extends CMTest_TestCase {

    public function testAddRecord() {
        $mockLogHandler = $this->mockInterface('CM_Log_Handler_HandlerInterface')->newInstance();
        $mockHandleRecord = $mockLogHandler->mockMethod('handleRecord');

        $logger = new CM_Log_Logger([$mockLogHandler]);

        $expectedRecord = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
        $mockHandleRecord->set(function (CM_Log_Record $record) use ($expectedRecord) {
            $this->assertSame($expectedRecord, $record);
        });

        $logger->addRecord($expectedRecord);
        $this->assertSame(1, $mockHandleRecord->getCallCount());
    }

    public function testAddRecordWithBubblingHandlers() {
        $mockLogHandlerFoo = $this->mockInterface('CM_Log_Handler_HandlerInterface')->newInstance();
        $mockLogHandlerBar = $this->mockInterface('CM_Log_Handler_HandlerInterface')->newInstance();

        $mockGetBubbleFoo = $mockLogHandlerFoo->mockMethod('getBubble');
        $mockGetBubbleBar = $mockLogHandlerBar->mockMethod('getBubble');
        $mockHandleRecordFoo = $mockLogHandlerFoo->mockMethod('handleRecord');
        $mockHandleRecordBar = $mockLogHandlerBar->mockMethod('handleRecord');

        $logger = new CM_Log_Logger([$mockLogHandlerFoo, $mockLogHandlerBar]);

        $mockGetBubbleFoo->set(false);
        $mockGetBubbleBar->set(false);
        $mockHandleRecordFoo->set(true);
        $mockHandleRecordBar->set(true);
        $logger->addRecord(new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context()));
        $this->assertSame(1, $mockHandleRecordFoo->getCallCount());
        $this->assertSame(0, $mockHandleRecordBar->getCallCount());

        $mockGetBubbleFoo->set(true);
        $mockGetBubbleBar->set(false);
        $mockHandleRecordFoo->set(true);
        $mockHandleRecordBar->set(true);
        $logger->addRecord(new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context()));
        $this->assertSame(2, $mockHandleRecordFoo->getCallCount());
        $this->assertSame(1, $mockHandleRecordBar->getCallCount());

        $mockGetBubbleFoo->set(false);
        $mockGetBubbleBar->set(false);
        $mockHandleRecordFoo->set(false);
        $mockHandleRecordBar->set(false);
        $logger->addRecord(new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context()));
        $this->assertSame(3, $mockHandleRecordFoo->getCallCount());
        $this->assertSame(2, $mockHandleRecordBar->getCallCount());
    }

    public function testLoggerAddHandlers() {
        /** @var CM_Log_Handler_Abstract $mockHandlerFoo */
        $mockHandlerFoo = $this->mockObject('CM_Log_Handler_Abstract');
        /** @var CM_Log_Handler_Abstract $mockHandlerBar */
        $mockHandlerBar = $this->mockObject('CM_Log_Handler_Abstract');

        $logger = new CM_Log_Logger();

        $logger->addHandlers([$mockHandlerFoo, $mockHandlerBar]);
        $this->assertTrue($mockHandlerFoo->getBubble());
        $this->assertFalse($mockHandlerBar->getBubble());

        /** @var CM_Log_Handler_Abstract $mockHandlerFooBar */
        $mockHandlerFooBar = $this->mockObject('CM_Log_Handler_Abstract');

        $logger->addHandlers([$mockHandlerFooBar]);
        $this->assertTrue($mockHandlerFoo->getBubble());
        $this->assertTrue($mockHandlerBar->getBubble());
        $this->assertFalse($mockHandlerFooBar->getBubble());

        /** @var CM_Log_Handler_Abstract $mockHandlerBarFoo */
        $mockHandlerBarFoo = $this->mockObject('CM_Log_Handler_Abstract');

        $logger->addHandler($mockHandlerBarFoo);
        $this->assertTrue($mockHandlerFoo->getBubble());
        $this->assertTrue($mockHandlerBar->getBubble());
        $this->assertTrue($mockHandlerFooBar->getBubble());
        $this->assertFalse($mockHandlerBarFoo->getBubble());
    }

    public function testLoggerAddFallback() {
        /** @var CM_Log_Handler_Abstract $mockHandlerFoo */
        $mockHandlerFoo = $this->mockObject('CM_Log_Handler_Abstract');
        /** @var CM_Log_Handler_Abstract $mockHandlerBar */
        $mockHandlerBar = $this->mockObject('CM_Log_Handler_Abstract');

        $logger = new CM_Log_Logger();

        $logger->addFallbacks([$mockHandlerFoo, $mockHandlerBar]);
        $this->assertFalse($mockHandlerFoo->getBubble());
        $this->assertFalse($mockHandlerBar->getBubble());

        /** @var CM_Log_Handler_Abstract $mockHandlerFooBar */
        $mockHandlerFooBar = $this->mockObject('CM_Log_Handler_Abstract');

        $logger->addFallbacks([$mockHandlerFooBar]);
        $this->assertFalse($mockHandlerFoo->getBubble());
        $this->assertFalse($mockHandlerBar->getBubble());
        $this->assertFalse($mockHandlerFooBar->getBubble());

        /** @var CM_Log_Handler_Abstract $mockHandlerBarFoo */
        $mockHandlerBarFoo = $this->mockObject('CM_Log_Handler_Abstract');

        $logger->addFallback($mockHandlerBarFoo);
        $this->assertFalse($mockHandlerFoo->getBubble());
        $this->assertFalse($mockHandlerBar->getBubble());
        $this->assertFalse($mockHandlerFooBar->getBubble());
        $this->assertFalse($mockHandlerBarFoo->getBubble());
    }

    public function testLogHelpers() {
        $mockLogHandler = $this->mockInterface('CM_Log_Handler_HandlerInterface')->newInstance();
        $mockHandleRecord = $mockLogHandler->mockMethod('handleRecord');

        $logger = new CM_Log_Logger([$mockLogHandler]);

        $count = 0;
        foreach (['debug', 'info', 'warning', 'error', 'critical'] as $helper) {
            $mockHandleRecord->set(function (CM_Log_Record $record) use ($helper) {
                $this->assertSame('message sent using ' . $helper . ' method', $record->getMessage());
                $this->assertSame(CM_Log_Logger::getLevelCode($helper), $record->getLevel());
            });

            $logger->$helper('message sent using ' . $helper . ' method');
            $this->assertSame(++$count, $mockHandleRecord->getCallCount());
        }
    }

    public function testLogException() {
        $mockLogHandler = $this->mockInterface('CM_Log_Handler_HandlerInterface')->newInstance();
        $mockHandleRecord = $mockLogHandler->mockMethod('handleRecord');

        $logger = new CM_Log_Logger([$mockLogHandler]);

        $exception = new Exception('foo');
        $mockHandleRecord->set(function (CM_Log_Record_Exception $record) use ($exception) {
            $contextException = $record->getException();
            $this->assertSame($exception->getMessage(), $contextException->getMessage());
            $this->assertSame($exception->getLine(), $contextException->getLine());
            $this->assertSame($exception->getFile(), $contextException->getFile());
            $this->assertSame('foo', $record->getMessage());
            $this->assertSame(CM_Log_Logger::NOTSET, $record->getLevel());
        });
        $logger->addException($exception);
        $this->assertSame(1, $mockHandleRecord->getCallCount());

        $exception = new CM_Exception('bar');
        $exception->setSeverity(CM_Exception::WARN);
        $mockHandleRecord->set(function (CM_Log_Record_Exception $record) use ($exception) {
            $contextException = $record->getException();
            $this->assertSame($exception->getMessage(), $contextException->getMessage());
            $this->assertSame($exception->getLine(), $contextException->getLine());
            $this->assertSame($exception->getFile(), $contextException->getFile());
            $this->assertSame('bar', $record->getMessage());
            $this->assertSame(CM_Log_Logger::WARNING, $record->getLevel());
        });
        $logger->addException($exception);
        $this->assertSame(2, $mockHandleRecord->getCallCount());

        $exception = new CM_Exception('foobar');
        $exception->setSeverity(CM_Exception::ERROR);
        $mockHandleRecord->set(function (CM_Log_Record_Exception $record) use ($exception) {
            $contextException = $record->getException();
            $this->assertSame($exception->getMessage(), $contextException->getMessage());
            $this->assertSame($exception->getLine(), $contextException->getLine());
            $this->assertSame($exception->getFile(), $contextException->getFile());
            $this->assertSame('foobar', $record->getMessage());
            $this->assertSame(CM_Log_Logger::ERROR, $record->getLevel());
        });
        $logger->addException($exception);
        $this->assertSame(3, $mockHandleRecord->getCallCount());

        $exception = new CM_Exception('barfoo');
        $exception->setSeverity(CM_Exception::FATAL);
        $mockHandleRecord->set(function (CM_Log_Record_Exception $record) use ($exception) {
            $contextException = $record->getException();
            $this->assertSame($exception->getMessage(), $contextException->getMessage());
            $this->assertSame($exception->getLine(), $contextException->getLine());
            $this->assertSame($exception->getFile(), $contextException->getFile());
            $this->assertSame('barfoo', $record->getMessage());
            $this->assertSame(CM_Log_Logger::CRITICAL, $record->getLevel());
        });
        $logger->addException($exception);
        $this->assertSame(4, $mockHandleRecord->getCallCount());
    }

    public function testStaticLogLevelMethods() {
        $this->assertSame(CM_Log_Logger::INFO, CM_Log_Logger::getLevelCode('info'));
        $this->assertSame('INFO', CM_Log_Logger::getLevelName(CM_Log_Logger::INFO));
        $this->assertNotEmpty(CM_Log_Logger::getLevels());
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage is not defined, use one of
     */
    public function testStaticGetLevelCodeException() {
        CM_Log_Logger::getLevelCode('foo');
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage is not defined, use one of
     */
    public function testStaticGetLevelNameException() {
        CM_Log_Logger::getLevelName(666);
    }
}
