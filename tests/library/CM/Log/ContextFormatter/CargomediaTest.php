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
        $this->assertSame('http://www.default.dev/foo?bar=1&baz=quux&viewInfoList=fooBar', $formattedContext['httpRequest']['uri']);
        $this->assertSame(join("\n", ['{',
            '    "bar": "1",',
            '    "baz": "quux",',
            '    "foo": "bar",',
            '    "quux": "baz"',
            '}']), $formattedContext['httpRequest']['query']);

        $this->assertSame('POST', $formattedContext['httpRequest']['method']);
        $this->assertSame('http://bar/baz', $formattedContext['httpRequest']['referer']);
        $this->assertSame('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_10)', $formattedContext['httpRequest']['useragent']);
        $this->assertSame('foo.bar', $formattedContext['httpRequest']['hostname']);
        $this->assertSame(['id' => $user->getId(), 'displayName' => 'user' . $user->getId()], $formattedContext['appName']['user']);
        $this->assertSame($clientId, $formattedContext['appName']['client']['id']);
        $this->assertSame('baz', $formattedContext['appName']['bar']);
        $this->assertSame('quux', $formattedContext['appName']['baz']);

        $this->assertSame('CM_Exception_Invalid', $formattedContext['exception']['type']);
        $this->assertSame('Bad', $formattedContext['exception']['message']);
        $this->assertArrayHasKey('stack', $formattedContext['exception']);
        $this->assertInternalType('string', $formattedContext['exception']['stack']);
        $this->assertSame(['foo' => "'bar'"], $formattedContext['exception']['metaInfo']);
        $this->assertRegExp('/library\/CM\/Log\/ContextFormatter\/CargomediaTest\.php\(\d+\)/', $formattedContext['exception']['stack']);
    }
}
