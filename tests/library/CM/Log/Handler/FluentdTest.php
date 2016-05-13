<?php

class CM_Log_Handler_FluentdTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testConstructor() {
        $fluentd = $this->mockClass('\Fluent\Logger\FluentLogger')->newInstanceWithoutConstructor();
        $handler = new CM_Log_Handler_Fluentd($fluentd, 'tag', 'appName');
        $this->assertInstanceOf('CM_Log_Handler_Fluentd', $handler);
    }

    public function testFormatting() {
        $level = CM_Log_Logger::DEBUG;
        $message = 'foo';
        $user = CMTest_TH::createUser();
        $httpRequest = CM_Http_Request_Abstract::factory(
            'post',
            '/foo?bar=1&baz=quux',
            ['bar' => 'baz'],
            [
                'http_referrer'   => 'http://bar/baz',
                'http_user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_10)',
                'foo'             => 'quux'
            ]
        );
        $clientId = $httpRequest->getClientId();
        $computerInfo = new CM_Log_Context_ComputerInfo('www.example.com', 'v7.0.1');
        $exception = new CM_Exception_Invalid('Bad');

        $appContext = new CM_Log_Context_App(['bar' => 'baz', 'baz' => 'quux'], $user, $exception);
        $record = new CM_Log_Record($level, $message, new CM_Log_Context($httpRequest, $computerInfo, $appContext));

        /** @var \Fluent\Logger\FluentLogger $fluentd */
        $fluentd = $this->mockClass('\Fluent\Logger\FluentLogger')->newInstanceWithoutConstructor();
        $handler = new CM_Log_Handler_Fluentd($fluentd, 'tag', 'appName');
        $formattedRecord = $this->callProtectedMethod($handler, '_formatRecord', [$record]);

        $this->assertSame($message, $formattedRecord['message']);
        $this->assertSame('debug', $formattedRecord['level']);
        $this->assertArrayHasKey('timestamp', $formattedRecord);
        $this->assertSame('www.example.com', $formattedRecord['computerInfo']['fqdn']);
        $this->assertSame('v7.0.1', $formattedRecord['computerInfo']['phpVersion']);
        $this->assertSame('/foo?bar=1&baz=quux', $formattedRecord['request']['uri']);

        $this->assertSame('POST', $formattedRecord['request']['method']);
        $this->assertSame('http://bar/baz', $formattedRecord['request']['referrer']);
        $this->assertSame('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_10)', $formattedRecord['request']['user_agent']);
        $this->assertSame($user->getId(), $formattedRecord['appName']['user']);
        $this->assertSame($clientId, $formattedRecord['appName']['clientId']);
        $this->assertSame('baz', $formattedRecord['appName']['bar']);
        $this->assertSame('quux', $formattedRecord['appName']['baz']);

        $this->assertSame('CM_Exception_Invalid', $formattedRecord['exception']['type']);
        $this->assertSame('Bad', $formattedRecord['exception']['message']);
        $this->assertArrayHasKey('stack', $formattedRecord['exception']);
        $this->assertInternalType('string', $formattedRecord['exception']['stack']);
        $this->assertRegExp('/CM_Log_Handler_FluentdTest->testFormatting\(\)/', $formattedRecord['exception']['stack']);
    }

    public function testWriteRecord() {
        $fluentd = $this->mockClass('\Fluent\Logger\FluentLogger')->newInstanceWithoutConstructor();
        $postMock = $fluentd->mockMethod('post')->set(
            function ($tag, array $data) {
                $this->assertSame('tag', $tag);
                $this->assertSame('critical', $data['level']);
                $this->assertSame('foo', $data['message']);
            }
        );
        /** @var \Fluent\Logger\FluentLogger $fluentd */

        $handler = new CM_Log_Handler_Fluentd($fluentd, 'tag', 'CM');

        $record = new CM_Log_Record(CM_Log_Logger::CRITICAL, 'foo', new CM_Log_Context());
        $this->callProtectedMethod($handler, '_writeRecord', [$record]);
        $this->assertSame(1, $postMock->getCallCount());
    }
}
