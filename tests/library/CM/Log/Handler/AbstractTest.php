<?php

class CM_Log_Handler_AbstractTest extends CMTest_TestCase {

    public function testHandlerLevel() {
        /** @var CM_Log_Handler_Abstract $mockLogHandler */
        $mockLogHandler = $this->mockClass('CM_Log_Handler_Abstract')->newInstanceWithoutConstructor();
        $mockHandleRecord = $mockLogHandler->mockMethod('_writeRecord');
        $mockHandleRecord->set(true);

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());

        $mockLogHandler->setLevelMin(CM_Log_Logger::DEBUG);
        $mockLogHandler->handleRecord($record);
        $this->assertSame(1, $mockHandleRecord->getCallCount());

        $mockLogHandler->setLevelMin(CM_Log_Logger::INFO);
        $mockLogHandler->handleRecord($record);
        $this->assertSame(2, $mockHandleRecord->getCallCount());

        $mockLogHandler->setLevelMin(CM_Log_Logger::CRITICAL);
        $mockLogHandler->handleRecord($record);
        $this->assertSame(2, $mockHandleRecord->getCallCount());
    }
}
