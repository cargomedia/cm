<?php

class CM_Log_Handler_StreamTest extends CMTest_TestCase {

    public function testWriteRecord() {
        /** @var CM_OutputStream_Interface|Mocka\ClassMock $mockStreamInterface */
        $mockStreamInterface = $this->mockInterface('CM_OutputStream_Interface')->newInstanceWithoutConstructor();
        /** @var Mocka\FunctionMock $mockWritelnMethod */
        $mockWritelnMethod = $mockStreamInterface->mockMethod('writeln');

        $mockWritelnMethod->set(function ($message) {
            $this->assertRegExp('/^\[[0-9T\:\-\+]+ - none - php none - INFO\] foo$/', $message);
        });

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
        $formatter = new CM_Log_Formatter_Text();
        $handler = new CM_Log_Handler_Stream($mockStreamInterface, $formatter);
        $this->forceInvokeMethod($handler, '_writeRecord', [$record]);

        $this->assertSame(1, $mockWritelnMethod->getCallCount());
    }

    public function testWriteRecordWithContext() {
        /** @var CM_OutputStream_Interface|Mocka\ClassMock $mockStreamInterface */
        $mockStreamInterface = $this->mockInterface('CM_OutputStream_Interface')->newInstanceWithoutConstructor();
        /** @var Mocka\FunctionMock $mockWritelnMethod */
        $mockWritelnMethod = $mockStreamInterface->mockMethod('writeln');

        $mockWritelnMethod->set(function ($message) {
            $this->assertRegExp('#^\[[0-9T\:\-\+]+ - none - php none - INFO\] foo\n - extra: foo: bar$#', $message);
        });

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context(null, null, null, ['foo' => 'bar']));
        $formatter = new CM_Log_Formatter_Text();
        $handler = new CM_Log_Handler_Stream($mockStreamInterface, $formatter);
        $this->forceInvokeMethod($handler, '_writeRecord', [$record]);

        $this->assertSame(1, $mockWritelnMethod->getCallCount());
    }

    public function testWriteRecordWithException() {
        /** @var CM_OutputStream_Interface|Mocka\ClassMock $mockStreamInterface */
        $mockStreamInterface = $this->mockInterface('CM_OutputStream_Interface')->newInstanceWithoutConstructor();
        /** @var Mocka\FunctionMock $mockWritelnMethod */
        $mockWritelnMethod = $mockStreamInterface->mockMethod('writeln');
        $mockWritelnMethod->set(function ($message) {
            $this->assertRegExp('#^\[[0-9T\:\-\+]+ - none - php none - CRITICAL\] Exception: foo\n - exception:.*$#s', $message);
        });

        $record = new CM_Log_Record_Exception(new Exception('foo'), new CM_Log_Context());
        $formatter = new CM_Log_Formatter_Text();
        $handler = new CM_Log_Handler_Stream($mockStreamInterface, $formatter);
        $this->forceInvokeMethod($handler, '_writeRecord', [$record]);

        $this->assertSame(1, $mockWritelnMethod->getCallCount());
    }
}
