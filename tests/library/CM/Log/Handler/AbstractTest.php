<?php

class CM_Log_Handler_AbstractTest extends CMTest_TestCase {

    public function testHandlerLevel() {
        /** @var CM_Log_Handler_Abstract $mockLogHandler */
        $mockLogHandler = $this->mockClass('CM_Log_Handler_Abstract')->newInstanceWithoutConstructor();
        $mockHandleRecord = $mockLogHandler->mockMethod('writeRecord')->set(true);

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());

        $mockLogHandler->setLevel(CM_Log_Logger::DEBUG);
        $this->assertTrue($mockLogHandler->handleRecord($record));
        $mockLogHandler->setLevel(CM_Log_Logger::INFO);
        $this->assertTrue($mockLogHandler->handleRecord($record));
        $mockLogHandler->setLevel(CM_Log_Logger::WARNING);
        $this->assertFalse($mockLogHandler->handleRecord($record));
    }
}
