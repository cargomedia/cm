<?php

class CM_Log_ContextFormatter_CargomediaTest extends CMTest_TestCase {

    public function testGetRecordContext() {
        $level = CM_Log_Logger::DEBUG;
        $message = 'foo';
        $user = CMTest_TH::createUser();
        $httpRequest = CM_Http_Request_Abstract::factory(
            'post',
            '/foo?bar=1&baz=quux&viewInfoList=fooBar',
            [
                'bar'  => 'baz',
                'host' => 'foo.bar:8080',
            ],
            [
                'http_referer'    => 'http://bar/baz',
                'http_user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_10)',
                'foo'             => 'quux',
            ],
            '{"foo" : "bar", "quux" : "baz"}'
        );
        $clientId = $httpRequest->getClientId();
        $computerInfo = new CM_Log_Context_ComputerInfo('www.example.com', 'v7.0.1');
        $exception = new CM_Exception_Invalid('Bad', null, ['foo' => 'bar']);

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
        $this->assertSame('/foo?bar=1&baz=quux&viewInfoList=fooBar', $formattedRecord['httpRequest']['uri']);
        $this->assertSame(
            [
                ['key' => 'bar', 'value' => '1'],
                ['key' => 'baz', 'value' => 'quux'],
                ['key' => 'foo', 'value' => 'bar'],
                ['key' => 'quux', 'value' => 'baz'],
            ],
            $formattedRecord['httpRequest']['query']
        );

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
        $this->assertSame(['foo' => "'bar'"], $formattedRecord['exception']['metaInfo']);
        $this->assertRegExp('/library\/CM\/Log\/ContextFormatter\/CargomediaTest\.php\(\d+\)/', $formattedRecord['exception']['stack']);
    }

    public function testArrayEncoding() {
        /** @var CM_Log_ContextFormatter_Cargomedia|\Mocka\AbstractClassTrait $mock */
        $mock = $this->mockClass('CM_Log_ContextFormatter_Cargomedia')->newInstanceWithoutConstructor();

        $this->assertSame([], CMTest_TH::callProtectedMethod($mock, '_encodeAsArray', [[]])); //empty array
        $array = [
            'foo4' => 'val4',
            'foo1' => ['bar1' => ['quux1' => 'val11', 'baz1' => 'val12']],
            'foo2' => ['bar2' => 'val21'],
            'foo3' => [4, '1', 3],
            'foo7' => ['bar4' => [1, 2]],
            'foo5' => '',
            'foo6' => [],
        ];
        $this->assertSame(
            [
                ['key' => 'foo1.bar1.baz1', 'value' => 'val12'],
                ['key' => 'foo1.bar1.quux1', 'value' => 'val11'],
                ['key' => 'foo2.bar2', 'value' => 'val21'],
                ['key' => 'foo3.0', 'value' => 4],
                ['key' => 'foo3.1', 'value' => '1'],
                ['key' => 'foo3.2', 'value' => 3],
                ['key' => 'foo4', 'value' => 'val4'],
                ['key' => 'foo5', 'value' => ''],
                ['key' => 'foo7.bar4.0', 'value' => 1],
                ['key' => 'foo7.bar4.1', 'value' => 2],
            ],
            CMTest_TH::callProtectedMethod($mock, '_encodeAsArray', [$array])
        );
    }
}
