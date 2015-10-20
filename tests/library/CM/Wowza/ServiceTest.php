<?php

class CM_Wowza_ServiceTest extends CMTest_TestCase {

    public function testSynchronize() {
    }

    public function testStopStream() {
        $streamChannel = $this->mockClass('CM_Model_StreamChannel_Abstract')->newInstanceWithoutConstructor();
        $streamChannel->mockMethod('getServerId')->set(5);
        /** @var CM_Model_StreamChannel_Abstract $streamChannel */

        $stream = $this->mockObject('CM_Model_Stream_Abstract');
        $stream->mockMethod('getStreamChannel')->set($streamChannel);
        $stream->mockMethod('getClientKey')->set('foo');
        /** @var CM_Model_Stream_Abstract $stream */

        $server = $this->mockClass('CM_MediaStreams_Server')->newInstanceWithoutConstructor();
        $configuration = $this->mockObject('configuration');
        $getServerMethod = $configuration->mockMethod('getServer')->set(function ($serverId) {
            $this->assertSame(5, $serverId);
        });
        /** @var CM_Wowza_Configuration $configuration */

        $httpClient = $this->mockObject('CM_Wowza_HttpClient');
        $stopClientMethod = $httpClient->mockMethod('stopClient')->set(function ($clientKey) {
            $this->assertSame('foo', $clientKey);
        });
        /** @var CM_Wowza_HttpApiClient $httpClient */

        $wowza = new CM_Wowza_Service($configuration, $httpClient);
        $this->callProtectedMethod($wowza, '_stopStream', [$stream]);
        $this->assertSame(1, $getServerMethod);
        $this->assertSame(1, $stopClientMethod);
    }
}
