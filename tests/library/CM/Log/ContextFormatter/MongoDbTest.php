<?php

class CM_Log_ContextFormatter_MongoDbTest extends CMTest_TestCase {

    public function testFormatContext() {
        $user = CMTest_TH::createUser();
        $httpRequest = CM_Http_Request_Abstract::factory('post', '/foo');
        $computerInfo = new CM_Log_Context_ComputerInfo('www.example.com', 'v7.0.1');
        $context = new CM_Log_Context();
        $context->setExtra([
            'bar' => 1,
            'a'   => new DateTime('2000-01-01'),
            'b'   => (object) ['foo' => 1],
            'c'   => new CM_Model_ContextFormatter_MongoDb_Mock(123, ['foo' => 'bar1']),
            'd'   => new CM_Frontend_JsonSerializable(['foo' => 'bar2']),
            'foo' => [
                'a' => new DateTime('2000-01-02'),
                'b' => (object) ['foo' => 2],
                'c' => new CM_Model_ContextFormatter_MongoDb_Mock(456, ['foo' => 'bar3']),
                'd' => new CM_Frontend_JsonSerializable([
                    'foo' => 'bar4',
                    'bar' => new DateTime('2000-01-03'),
                    'baz' => new CM_Model_ContextFormatter_MongoDb_Mock(789, ['foo' => 'bar4']),
                ]),
            ]
        ]);
        $context->setUser($user);
        $context->setComputerInfo($computerInfo);
        $context->setHttpRequest($httpRequest);

        $contextFormatter = new CM_Log_ContextFormatter_MongoDb();
        $formattedContext = $contextFormatter->formatContext($context);

        $this->assertSame([
            'fqdn'       => 'www.example.com',
            'phpVersion' => 'v7.0.1',
        ], $formattedContext['computerInfo']);
        $this->assertSame([
            'method'   => 'POST',
            'uri'      => '/foo',
            'query'    => [],
            'server'   => [],
            'headers'  => [],
            'body'     => '',
            'clientId' => 1,
        ], $formattedContext['httpRequest']);
        $this->assertSame([
            'id'   => $user->getId(),
            'name' => $user->getDisplayName(),
        ], $formattedContext['user']);

        $extra = $formattedContext['extra'];
        $this->assertInstanceOf('MongoDate', $extra['a']);
        $this->assertInstanceOf('MongoDate', $extra['foo']['a']);
        $this->assertInstanceOf('MongoDate', $extra['foo']['d']['bar']);
        unset($extra['a']);
        unset($extra['foo']['a']);
        unset($extra['foo']['d']['bar']);
        $this->assertSame([
            'bar'  => 1,
            'b'    => '[stdClass]',
            'c'    => '[CM_Model_ContextFormatter_MongoDb_Mock:123]',
            'd'    => [
                'foo' => 'bar2',
            ],
            'foo'  => [
                'b' => '[stdClass]',
                'c' => '[CM_Model_ContextFormatter_MongoDb_Mock:456]',
                'd' => [
                    'foo' => 'bar4',
                    'baz' => '[CM_Model_ContextFormatter_MongoDb_Mock:789]',
                ],
            ],
            'type' => CM_Log_ContextFormatter_MongoDb::DEFAULT_TYPE,
        ], $extra);
    }

    public function testFormatAppContext() {
        $formatter = new CM_Log_ContextFormatter_MongoDb();
        $exception = $this->catchException(function () use ($formatter) {
            $formatter->formatAppContext(new CM_Log_Context());
        });
        $this->assertInstanceOf('CM_Exception_NotImplemented', $exception);
    }
}

class CM_Model_ContextFormatter_MongoDb_Mock extends CM_Model_Abstract {

    protected function _getData() {
        return [];
    }

    public static function getTypeStatic() {
        return 1;
    }
}
