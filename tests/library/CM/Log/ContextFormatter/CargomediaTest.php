<?php


class CM_Log_ContextFormatter_CargomediaTest extends CMTest_TestCase {

    public function testGetRecordContext() {
        $level = CM_Log_Logger::DEBUG;
        $message = 'foo';
        $user = CMTest_TH::createUser();
        $httpRequest = CM_Http_Request_Abstract::factory(
            'post',
            '/foo?bar=1&baz=quux',
            [
                'bar'  => 'baz',
                'host' => 'foo.bar:8080',
            ],
            [
                'http_referer'   => 'http://bar/baz',
                'http_user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_10)',
                'foo'             => 'quux',
            ]
        );
        $clientId = $httpRequest->getClientId();
        $computerInfo = new CM_Log_Context_ComputerInfo('www.example.com', 'v7.0.1');
        $exception = new CM_Exception_Invalid('Bad');

        $context = new CM_Log_Context();
        $context->setExtra(['bar' => 'baz', 'baz' => 'quux']);
        $context->setUser($user);
        $context->setException($exception);
        $context->setComputerInfo($computerInfo);
        $context->setHttpRequest($httpRequest);
        $record = new CM_Log_Record($level, $message, $context);

        $contextFormatter = new CM_Log_ContextFormatter_Cargomedia('appName');
        $formattedRecord = $contextFormatter->formatRecordContext($record);
        
        $this->assertSame($message, $formattedRecord['message']);
        $this->assertSame('debug', $formattedRecord['level']);
        $this->assertArrayHasKey('timestamp', $formattedRecord);
        $this->assertSame('www.example.com', $formattedRecord['computerInfo']['fqdn']);
        $this->assertSame('v7.0.1', $formattedRecord['computerInfo']['phpVersion']);
        $this->assertSame('/foo?bar=1&baz=quux', $formattedRecord['httpRequest']['uri']);

        $this->assertSame('POST', $formattedRecord['httpRequest']['method']);
        $this->assertSame('http://bar/baz', $formattedRecord['httpRequest']['referer']);
        $this->assertSame('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_10)', $formattedRecord['httpRequest']['useragent']);
        $this->assertSame('foo.bar', $formattedRecord['httpRequest']['hostname']);
        $this->assertSame($user->getId(), $formattedRecord['appName']['user']);
        $this->assertSame($clientId, $formattedRecord['appName']['clientId']);
        $this->assertSame('baz', $formattedRecord['appName']['bar']);
        $this->assertSame('quux', $formattedRecord['appName']['baz']);

        $this->assertSame('CM_Exception_Invalid', $formattedRecord['exception']['type']);
        $this->assertSame('Bad', $formattedRecord['exception']['message']);
        $this->assertArrayHasKey('stack', $formattedRecord['exception']);
        $this->assertInternalType('string', $formattedRecord['exception']['stack']);
        $this->assertRegExp('/CM_Log_ContextFormatter_CargomediaTest->testGetRecordContext\(\)/', $formattedRecord['exception']['stack']);
    }
}
