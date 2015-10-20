<?php

class CM_Wowza_ServiceTest extends CMTest_TestCase {

    public function testStopStream() {
        $streamChannel = $this->mockClass('CM_Model_StreamChannel_Abstract')->newInstanceWithoutConstructor();
        $streamChannel->mockMethod('getServerId')->set(5);
        /** @var CM_Model_StreamChannel_Abstract $streamChannel */

        $stream = $this->mockObject('CM_Model_Stream_Abstract');
        $stream->mockMethod('getStreamChannel')->set($streamChannel);
        $stream->mockMethod('getKey')->set('foo');
        /** @var CM_Model_Stream_Abstract $stream */

        $server = $this->mockClass('CM_Wowza_Server')->newInstanceWithoutConstructor();
        $configuration = $this->mockObject('CM_Wowza_Configuration');
        $configuration->mockMethod('getServer')->set(function ($serverId) use ($server) {
            $this->assertSame(5, $serverId);
            return $server;
        });
        /** @var CM_Wowza_Configuration $configuration */

        $httpClient = $this->mockClass('CM_Wowza_HttpApiClient')->newInstanceWithoutConstructor();
        $stopClientMethod = $httpClient->mockMethod('stopClient')->set(function ($passedServer, $clientKey) use ($server) {
            $this->assertSame('foo', $clientKey);
            $this->assertSame($server, $passedServer);
        });
        /** @var CM_Wowza_HttpApiClient $httpClient */

        $wowza = new CM_Wowza_Service($configuration, $httpClient);
        $this->callProtectedMethod($wowza, '_stopStream', [$stream]);
        $this->assertSame(1, $stopClientMethod->getCallCount());
    }
}
