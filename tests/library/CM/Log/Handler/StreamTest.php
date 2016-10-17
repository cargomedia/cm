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
        $message = 'foo';
        $exceptionMessage = 'foo!';
        $extra = ['foo', 'bar' => true, 'foo' => ['foobar' => 1]];

        /** @var CM_OutputStream_Interface|Mocka\ClassMock $mockStreamInterface */
        $mockStreamInterface = $this->mockInterface('CM_OutputStream_Interface')->newInstanceWithoutConstructor();
        /** @var Mocka\FunctionMock $mockWritelnMethod */
        $mockWritelnMethod = $mockStreamInterface->mockMethod('writeln');
        $mockWritelnMethod->set(function ($outputText) use ($message, $exceptionMessage, $extra) {
            $this->assertRegExp('/^\[[0-9T\:\-\+]+ - none - php none - INFO\] ' . $message . '\n/s', $outputText);
            $this->assertRegExp('/\n - extra: ' . json_encode($extra, JSON_PRETTY_PRINT) . '/s', $outputText);
            $this->assertRegExp('/\n - exception:(?:\s+) - message: ' . $exceptionMessage . '.*$/s', $outputText);
        });

        $context = new CM_Log_Context();
        $context->setExtra($extra);
        $context->setException(new Exception($exceptionMessage));
        $record = new CM_Log_Record(CM_Log_Logger::INFO, $message, $context);
        $formatter = new CM_Log_Formatter_Text();
        $handler = new CM_Log_Handler_Stream($mockStreamInterface, $formatter);
        $this->forceInvokeMethod($handler, '_writeRecord', [$record]);

        $this->assertSame(1, $mockWritelnMethod->getCallCount());
    }
}
