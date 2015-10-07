<?php

class CM_Log_Handler_NewrelicTest extends CMTest_TestCase {

    public function testWriteRecordWithLogRecordException() {
        $expectedException = new Exception('foo');

        /** @var CMService_Newrelic|Mocka\AbstractClassTrait $mockStreamInterface */
        $newrelic = $this->mockClass('CMService_Newrelic')->newInstanceWithoutConstructor();
        /** @var Mocka\FunctionMock $methodSetNoticeError */
        $methodSetNoticeError = $newrelic->mockMethod('setNoticeError');

        $methodSetNoticeError->set(function (Exception $exception) use ($expectedException) {
            $this->assertEquals($expectedException, $exception);
        });

        $record = new CM_Log_Record_Exception($expectedException, new CM_Log_Context());
        $handler = new CM_Log_Handler_Newrelic($newrelic);
        $this->forceInvokeMethod($handler, '_writeRecord', [$record]);

        $this->assertSame(1, $methodSetNoticeError->getCallCount());
    }

    public function testWriteRecordWithLogRecord() {
        /** @var CMService_Newrelic|Mocka\AbstractClassTrait $mockStreamInterface */
        $newrelic = $this->mockClass('CMService_Newrelic')->newInstanceWithoutConstructor();
        /** @var Mocka\FunctionMock $methodSetNoticeError */
        $methodSetNoticeError = $newrelic->mockMethod('setNoticeError');

        $methodSetNoticeError->set(function () {
            $this->fail('CMService_Newrelic::setNoticeError should not have been called.');
        });

        $record = new CM_Log_Record(CM_Log_Logger::CRITICAL, 'foo', new CM_Log_Context());
        $handler = new CM_Log_Handler_Newrelic($newrelic);

        try {
            $this->forceInvokeMethod($handler, '_writeRecord', [$record]);
            $this->fail('CM_Exception_Invalid not caught.');
        } catch (CM_Exception_Invalid $exception) {
            $this->assertSame('`CM_Log_Record` is not supported by `CM_Log_Handler_Newrelic`.', $exception->getMessage());
        }
    }

    public function testIsHandling() {
        /** @var CMService_Newrelic|Mocka\AbstractClassTrait $mockStreamInterface */
        $newrelic = $this->mockClass('CMService_Newrelic')->newInstanceWithoutConstructor();

        $context = new CM_Log_Context();

        $handler = new CM_Log_Handler_Newrelic($newrelic);
        $record = new CM_Log_Record(CM_Log_Logger::CRITICAL, 'foo', $context);
        $this->assertFalse($handler->isHandling($record));
        $record = new CM_Log_Record_Exception(new CM_Exception('warning exception', CM_Exception::WARN), $context);
        $this->assertFalse($handler->isHandling($record));
        $record = new CM_Log_Record_Exception(new CM_Exception('error exception', CM_Exception::ERROR), $context);
        $this->assertTrue($handler->isHandling($record));
        $record = new CM_Log_Record_Exception(new Exception('native exception'), $context);
        $this->assertTrue($handler->isHandling($record));

        $handler = new CM_Log_Handler_Newrelic($newrelic, CM_Log_Logger::WARNING);
        $record = new CM_Log_Record_Exception(new CM_Exception('warning exception', CM_Exception::WARN), $context);
        $this->assertTrue($handler->isHandling($record));
        $record = new CM_Log_Record_Exception(new CM_Exception('error exception', CM_Exception::ERROR), $context);
        $this->assertTrue($handler->isHandling($record));
    }
}
