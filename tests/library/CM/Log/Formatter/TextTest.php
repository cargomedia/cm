<?php

class CM_Log_Formatter_TextTest extends CMTest_TestCase {

    public function testRenderMessage() {
        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
        $formatter = new CM_Log_Formatter_Text();
        $this->assertRegExp('/^\[[0-9T\:\-\+]+ - none - php none - INFO\] foo$/', $formatter->renderMessage($record));
    }

    public function testFormatMessageCustomized() {
        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
        $formatter = new CM_Log_Formatter_Text('{message} | {datetime} | {levelname}', 'H:i:s');
        $this->assertRegExp('/^foo \| [0-9]{2}:[0-9]{2}:[0-9]{2} \| INFO$/', $formatter->renderMessage($record));
    }

    public function testFormatMessageComputerInfo() {
        $formatter = new CM_Log_Formatter_Text();
        $computerInfo = new CM_Log_Context_ComputerInfo('foo.com', '5.4');
        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context(null, $computerInfo));
        $this->assertRegExp('/^\[[0-9T\:\-\+]+ - foo.com - php 5.4 - INFO\] foo$/', $formatter->renderMessage($record));
    }

    public function testFormatContextWithHttpRequest() {
        /** @var CM_Http_Request_Abstract|\Mocka\ClassMock $mockHttpRequest */
        $mockHttpRequest = $this->mockClass('CM_Http_Request_Abstract')->newInstance(['', [
            'referer'    => 'http://foo.com/foo',
            'user-agent' => 'Mozilla/5.0',
        ]]);

        // can't mock final getPath...
        $mockHttpRequest->setPath('/foo/bar');
        $mockHttpRequest->mockMethod('getServer')->set(['REQUEST_METHOD' => 'GET', 'SERVER_PROTOCOL' => 'HTTP/1.1']);
        $mockHttpRequest->mockMethod('getHost')->set('foo.com');
        $mockHttpRequest->mockMethod('getIp')->set('10.10.0.1');

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context($mockHttpRequest));
        $formatter = new CM_Log_Formatter_Text();
        $this->assertSame(
            ' - httpRequest: GET /foo/bar HTTP/1.1, host: foo.com, ip: 10.10.0.1, referer: http://foo.com/foo, user-agent: Mozilla/5.0',
            $formatter->renderContext($record));
    }

    public function testFormattingWithExtra() {
        $extra = ['foo', 'bar', 'foo' => 'bar'];
        $appContext = new CM_Log_Context_App($extra);
        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context(null, null, $appContext));
        $formatter = new CM_Log_Formatter_Text();
        $this->assertSame(' - extra: 0: foo, 1: bar, foo: bar', $formatter->renderContext($record));
    }

    public function testFormattingWithException() {
        $exception = new Exception('foo');
        $record = new CM_Log_Record_Exception($exception, new CM_Log_Context());
        $formatter = new CM_Log_Formatter_Text();
        $messageLines = explode(PHP_EOL, $formatter->renderException($record));

        $this->assertSame(' - exception:', $messageLines[0]);
        $this->assertSame('   - message: foo', $messageLines[1]);
        $this->assertSame('   - type: Exception', $messageLines[2]);
        $this->assertSame('   - stacktrace: ', $messageLines[3]);
        $this->assertRegExp('/^[ ]{5}[0-9]{2}. [^ ]+ [^ ]+?:[0-9]*$/', $messageLines[4]);
    }
}
