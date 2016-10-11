<?php

class CM_Log_ContextFormatter_CargomediaTest extends CMTest_TestCase {

    public function testGetRecordContext() {
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

        $contextFormatter = new CM_Log_ContextFormatter_Cargomedia('appName');
        $formattedContext = $contextFormatter->formatContext($context);

        $this->assertSame('www.example.com', $formattedContext['computerInfo']['fqdn']);
        $this->assertSame('v7.0.1', $formattedContext['computerInfo']['phpVersion']);
        $this->assertSame('/foo?bar=1&baz=quux&viewInfoList=fooBar', $formattedContext['httpRequest']['uri']);
        $this->assertSame(
            [
                ['key' => 'bar', 'value' => '1'],
                ['key' => 'baz', 'value' => 'quux'],
                ['key' => 'foo', 'value' => 'bar'],
                ['key' => 'quux', 'value' => 'baz'],
            ],
            $formattedContext['httpRequest']['query']
        );

        $this->assertSame('POST', $formattedContext['httpRequest']['method']);
        $this->assertSame('http://bar/baz', $formattedContext['httpRequest']['referer']);
        $this->assertSame('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_10)', $formattedContext['httpRequest']['useragent']);
        $this->assertSame('foo.bar', $formattedContext['httpRequest']['hostname']);
        $this->assertSame($user->getId(), $formattedContext['appName']['user']);
        $this->assertSame($clientId, $formattedContext['appName']['clientId']);
        $this->assertSame('baz', $formattedContext['appName']['bar']);
        $this->assertSame('quux', $formattedContext['appName']['baz']);

        $this->assertSame('CM_Exception_Invalid', $formattedContext['exception']['type']);
        $this->assertSame('Bad', $formattedContext['exception']['message']);
        $this->assertArrayHasKey('stack', $formattedContext['exception']);
        $this->assertInternalType('string', $formattedContext['exception']['stack']);
        $this->assertSame(['foo' => "'bar'"], $formattedContext['exception']['metaInfo']);
        $this->assertRegExp('/library\/CM\/Log\/ContextFormatter\/CargomediaTest\.php\(\d+\)/', $formattedContext['exception']['stack']);
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

        $array = [
            [
                4,
                5,
                6,
                [1, 2, 3]
            ],
            [1, 2, 3],
            [7, 8]
        ];
        $this->assertSame(
            [
                ['key' => '0.0', 'value' => 4],
                ['key' => '0.1', 'value' => 5],
                ['key' => '0.2', 'value' => 6],
                ['key' => '0.3.0', 'value' => 1],
                ['key' => '0.3.1', 'value' => 2],
                ['key' => '0.3.2', 'value' => 3],
                ['key' => '1.0', 'value' => 1],
                ['key' => '1.1', 'value' => 2],
                ['key' => '1.2', 'value' => 3],
                ['key' => '2.0', 'value' => 7],
                ['key' => '2.1', 'value' => 8],
            ],
            CMTest_TH::callProtectedMethod($mock, '_encodeAsArray', [$array])
        );
    }
}
