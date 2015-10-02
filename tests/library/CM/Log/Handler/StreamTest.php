<?php

class CM_Log_Handler_StreamTest extends CMTest_TestCase {

    public function testFormatting() {
        /** @var CM_OutputStream_Interface|Mocka\ClassMock $mockStreamInterface */
        $mockStreamInterface = $this->mockInterface('CM_OutputStream_Interface')->newInstanceWithoutConstructor();

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
        $handler = new CM_Log_Handler_Stream($mockStreamInterface);
        $formattedMessage = $this->forceInvokeMethod($handler, '_formatRecord', [$record]);
        $this->assertRegExp('/^\[[0-9T\:\-\+]+ - INFO\] foo$/', $formattedMessage);
    }

    public function testFormattingCustomFormat() {
        /** @var CM_OutputStream_Interface|Mocka\ClassMock $mockStreamInterface */
        $mockStreamInterface = $this->mockInterface('CM_OutputStream_Interface')->newInstanceWithoutConstructor();

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
        $handler = new CM_Log_Handler_Stream($mockStreamInterface, CM_Log_Logger::INFO, '{message} | {datetime} | {levelname}', 'H:i:s');
        $formattedMessage = $this->forceInvokeMethod($handler, '_formatRecord', [$record]);
        $this->assertRegExp('/^foo \| [0-9]{2}:[0-9]{2}:[0-9]{2} \| INFO$/', $formattedMessage);
    }

    public function testFormattingWithUser() {
        /** @var CM_OutputStream_Interface|Mocka\ClassMock $mockStreamInterface */
        $mockStreamInterface = $this->mockInterface('CM_OutputStream_Interface')->newInstanceWithoutConstructor();

        $mockUser = $this->mockObject('CM_Model_User');
        $mockUser->mockMethod('getId')->set(1);
        $mockUser->mockMethod('getEmail')->set('foo@bar.com');

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context($mockUser));
        $handler = new CM_Log_Handler_Stream($mockStreamInterface);
        $formattedMessage = $this->forceInvokeMethod($handler, '_formatRecord', [$record]);
        $messageLines = explode(PHP_EOL, $formattedMessage);

        $this->assertRegExp('/^\[[0-9T\:\-\+]+ - INFO\] foo$/', $messageLines[0]);
        $this->assertSame(' - user: id: 1, email: foo@bar.com', $messageLines[1]);
    }

    public function testFormattingWithHttpRequest() {
        /** @var CM_OutputStream_Interface|Mocka\ClassMock $mockStreamInterface */
        $mockStreamInterface = $this->mockInterface('CM_OutputStream_Interface')->newInstanceWithoutConstructor();

        $mockHttpRequest = $this->mockClass('CM_Http_Request_Abstract')->newInstance(['', [
            'referer'    => 'http://foo.com/foo',
            'user-agent' => 'Mozilla/5.0',
        ]]);

        // can't mock final getPath...
        $mockHttpRequest->setPath('/foo/bar');
        $mockHttpRequest->mockMethod('getServer')->set(['REQUEST_METHOD' => 'GET', 'SERVER_PROTOCOL' => 'HTTP/1.1']);
        $mockHttpRequest->mockMethod('getHost')->set('foo.com');
        $mockHttpRequest->mockMethod('getIp')->set('10.10.0.1');

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context(null, $mockHttpRequest));
        $handler = new CM_Log_Handler_Stream($mockStreamInterface);
        $formattedMessage = $this->forceInvokeMethod($handler, '_formatRecord', [$record]);
        $messageLines = explode(PHP_EOL, $formattedMessage);

        $this->assertRegExp('/^\[[0-9T\:\-\+]+ - INFO\] foo$/', $messageLines[0]);
        $this->assertSame(
            ' - httpRequest: GET /foo/bar HTTP/1.1, host: foo.com, ip: 10.10.0.1, referer: http://foo.com/foo, user-agent: Mozilla/5.0',
            $messageLines[1]);
    }

    public function testFormattingWithExtra() {
        /** @var CM_OutputStream_Interface|Mocka\ClassMock $mockStreamInterface */
        $mockStreamInterface = $this->mockInterface('CM_OutputStream_Interface')->newInstanceWithoutConstructor();

        $extra = ['foo', 'bar', 'foo' => 'bar'];

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context(null, null, null, $extra));
        $handler = new CM_Log_Handler_Stream($mockStreamInterface);
        $formattedMessage = $this->forceInvokeMethod($handler, '_formatRecord', [$record]);
        $messageLines = explode(PHP_EOL, $formattedMessage);

        $this->assertRegExp('/^\[[0-9T\:\-\+]+ - INFO\] foo$/', $messageLines[0]);
        $this->assertSame(' - extra: 0: foo, 1: bar, foo: bar', $messageLines[1]);
    }

    public function testFormattingWithException() {
        /** @var CM_OutputStream_Interface|Mocka\ClassMock $mockStreamInterface */
        $mockStreamInterface = $this->mockInterface('CM_OutputStream_Interface')->newInstanceWithoutConstructor();

        $exception = new Exception('foo');

        $record = new CM_Log_Record_Exception($exception, new CM_Log_Context());
        $handler = new CM_Log_Handler_Stream($mockStreamInterface);
        $formattedMessage = $this->forceInvokeMethod($handler, '_formatRecord', [$record]);
        $messageLines = explode(PHP_EOL, $formattedMessage);

        $this->assertRegExp('/^\[[0-9T\:\-\+]+ - ERROR\] foo$/', $messageLines[0]);
        $this->assertSame(' - exception: ', $messageLines[1]);
        $this->assertSame('   - message: foo', $messageLines[2]);
        $this->assertSame('   - type: Exception', $messageLines[3]);
        $this->assertSame('   - stacktrace: ', $messageLines[4]);
        $this->assertRegExp('/^[ ]{5}[0-9]{2}. [^ ]+ [^ ]+?:[0-9]*$/', $messageLines[5]);
    }

    public function testWriteRecord() {
        /** @var CM_OutputStream_Interface|Mocka\ClassMock $mockStreamInterface */
        $mockStreamInterface = $this->mockInterface('CM_OutputStream_Interface')->newInstanceWithoutConstructor();
        /** @var Mocka\FunctionMock $mockWritelnMethod */
        $mockWritelnMethod = $mockStreamInterface->mockMethod('writeln');

        $mockWritelnMethod->set(function ($message) {
            $this->assertRegExp('/^\[[0-9T\:\-\+]+ - INFO\] foo$/', $message);
        });

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
        $handler = new CM_Log_Handler_Stream($mockStreamInterface);
        $this->forceInvokeMethod($handler, '_writeRecord', [$record]);

        $this->assertSame(1, $mockWritelnMethod->getCallCount());
    }
}
