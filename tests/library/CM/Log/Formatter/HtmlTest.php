<?php

class CM_Log_Formatter_HtmlTest extends CMTest_TestCase {

    public function testRenderMessage() {
        $formatter = new CM_Log_Formatter_Html();
        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
        $this->assertSame('<h2>foo (fqdn: none, php version: none)</h2>', $formatter->renderMessage($record));
    }

    public function testRenderExceptionMessage() {
        $formatter = new CM_Log_Formatter_Html();
        $record = new CM_Log_Record_Exception(new Exception('foo'), new CM_Log_Context());
        $this->assertSame('<h1>Exception</h1><h2>foo (fqdn: none, php version: none)</h2>', $formatter->renderMessage($record));
    }

    public function testFormatMessageContext() {
        $formatter = new CM_Log_Formatter_Html();
        $computerInfo = new CM_Log_Context_ComputerInfo('foo.com', '5.4');
        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context(null, null, $computerInfo));
        $this->assertSame('<h2>foo (fqdn: foo.com, php version: 5.4)</h2>', $formatter->renderMessage($record));
    }

    public function testFormatContextWithHttpRequest() {
        $formatter = new CM_Log_Formatter_Html();

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
        $this->assertSame(
            '<h3>Context:</h3><pre> - httpRequest: GET /foo/bar HTTP/1.1, host: foo.com, ip: 10.10.0.1, referer: http://foo.com/foo, user-agent: Mozilla/5.0</pre>',
            $formatter->renderContext($record));
    }

    public function testFormattingWithException() {
        $formatter = new CM_Log_Formatter_Html();

        $exception = new Exception('foo');
        $record = new CM_Log_Record_Exception($exception, new CM_Log_Context());
        $this->assertRegExp('#<h3>Exception:</h3><pre>.*?</pre>#s', $formatter->renderException($record));
    }
}
