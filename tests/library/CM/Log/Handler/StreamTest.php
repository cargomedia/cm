<?php

class CM_Log_Handler_StreamTest extends CMTest_TestCase {

    public function testFormatting() {
        /** @var CM_OutputStream_Interface|Mocka\ClassMock $mockStreamInterface */
        $mockStreamInterface = $this->mockInterface('CM_OutputStream_Interface')->newInstanceWithoutConstructor();

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
        $handler = new CM_Log_Handler_Stream($mockStreamInterface);
        $formattedMessage = $this->forceInvokeMethod($handler, '_formatRecord', [$record]);
        $this->assertRegExp('/^\[[0-9]{2}-[0-9]{2}-[0-9]{4} [0-9]{2}:[0-9]{2}:[0-9]{2} - INFO\] foo$/', $formattedMessage);

        $handler = new CM_Log_Handler_Stream($mockStreamInterface, CM_Log_Logger::INFO, '{message} | {datetime} | {levelname}', 'H:i:s');
        $formattedMessage = $this->forceInvokeMethod($handler, '_formatRecord', [$record]);
        $this->assertRegExp('/^foo \| [0-9]{2}:[0-9]{2}:[0-9]{2} \| INFO$/', $formattedMessage);
    }

    public function testWriteRecord() {
        /** @var CM_OutputStream_Interface|Mocka\ClassMock $mockStreamInterface */
        $mockStreamInterface = $this->mockInterface('CM_OutputStream_Interface')->newInstanceWithoutConstructor();
        /** @var Mocka\FunctionMock $mockWritelnMethod */
        $mockWritelnMethod = $mockStreamInterface->mockMethod('writeln');

        $mockWritelnMethod->set(function($message){
            $this->assertRegExp('/^\[[0-9 \:\-]+INFO\] foo$/', $message);
        });

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
        $handler = new CM_Log_Handler_Stream($mockStreamInterface);
        $this->forceInvokeMethod($handler, '_writeRecord', [$record]);

        $this->assertSame(1, $mockWritelnMethod->getCallCount());
    }
}
