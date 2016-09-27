<?php

class CM_Log_ContextFormatter_FluentdTest extends CMTest_TestCase {

    public function testFormatAppContext() {
        $user = CMTest_TH::createUser();
        $httpRequest = CM_Http_Request_Abstract::factory('post', '/foo');
        $computerInfo = new CM_Log_Context_ComputerInfo('www.example.com', 'v7.0.1');
        $context = new CM_Log_Context();
        $context->setExtra([
            'bar' => 1,
            'a'   => new DateTime('2000-01-01'),
            'b'   => (object) ['foo' => 1],
            'c'   => new CM_ModelMock(123, ['foo' => 'bar1']),
            'd'   => new CM_Frontend_JsonSerializable(['foo' => 'bar2']),
            'foo' => [
                'a' => new DateTime('2000-01-02'),
                'b' => (object) ['foo' => 2],
                'c' => new CM_ModelMock(456, ['foo' => 'bar3']),
                'd' => new CM_Frontend_JsonSerializable(['foo' => 'bar4']),
            ]
        ]);
        $context->setUser($user);
        $context->setComputerInfo($computerInfo);
        $context->setHttpRequest($httpRequest);

        $contextFormatter = new CM_Log_ContextFormatter_Fluentd('appName');
        $formattedContext = $contextFormatter->formatAppContext($context);

        $this->assertSame([
            'appName' => [
                'bar'      => 1,
                'a'        => '2000-01-01T00:00:00+00:00',
                'b'        => '[stdClass]',
                'c'        => '[CM_ModelMock:123]',
                'd'        => '[CM_Frontend_JsonSerializable]',
                'foo'      => [
                    'a' => '2000-01-02T00:00:00+00:00',
                    'b' => '[stdClass]',
                    'c' => '[CM_ModelMock:456]',
                    'd' => '[CM_Frontend_JsonSerializable]',
                ],
                'user'     => $user->getId(),
                'clientId' => $httpRequest->getClientId(),
            ]
        ], $formattedContext);
    }
}

class CM_ModelMock extends CM_Model_Abstract {

    protected function _getData() {
        return [];
    }

    public static function getTypeStatic() {
        return 1;
    }
}
