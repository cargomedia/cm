<?php

class CM_Janus_ServiceTest extends CMTest_TestCase {

    public function testFetchStatus() {
        $configuration = new CM_Janus_Configuration();
        foreach ([1, 5] as $serverId) {
            $server = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
            $server->mockMethod('getId')->set($serverId);
            /** @var CM_Janus_Server $server */
            $configuration->addServer($server);
        }
        $httpApiClient = $this->mockClass('CM_Janus_HttpApiClient')->newInstanceWithoutConstructor();
        $httpApiClient->mockMethod('fetchStatus')->set(function (CM_Janus_Server $server) {
            switch ($server->getId()) {
                case 1:
                    return [
                        ['streamKey' => 'stream-foo', 'streamChannelKey' => 'channel-foo'],
                    ];
                case 5:
                    return [
                        ['streamKey' => 'stream-bar', 'streamChannelKey' => 'channel-bar'],
                        ['streamKey' => 'stream-zoo', 'streamChannelKey' => 'channel-zoo'],
                    ];
            }
        });
        /** @var CM_Janus_HttpApiClient $httpApiClient */

        $janus = new CM_Janus_Service($configuration, $httpApiClient);
        /** @var CM_Janus_Stream[] $streams */
        $streams = $this->callProtectedMethod($janus, '_fetchStatus');
        $this->assertContainsOnlyInstancesOf('CM_Janus_Stream', $streams);
        $this->assertCount(3, $streams);

        $this->assertSame($configuration->getServer(1), $streams[0]->getServer());
        $this->assertSame('stream-foo', $streams[0]->getStreamKey());
        $this->assertSame('channel-foo', $streams[0]->getStreamChannelKey());

        $this->assertSame($configuration->getServer(5), $streams[1]->getServer());
        $this->assertSame('stream-bar', $streams[1]->getStreamKey());
        $this->assertSame('channel-bar', $streams[1]->getStreamChannelKey());

        $this->assertSame($configuration->getServer(5), $streams[2]->getServer());
        $this->assertSame('stream-zoo', $streams[2]->getStreamKey());
        $this->assertSame('channel-zoo', $streams[2]->getStreamChannelKey());
    }

    public function testStopStream() {
        $streamChannel = $this->mockClass('CM_Model_StreamChannel_Abstract')->newInstanceWithoutConstructor();
        $streamChannel->mockMethod('getServerId')->set(1);

        $stream = $this->mockClass('CM_Model_Stream_Abstract')->newInstanceWithoutConstructor();
        $stream->mockMethod('getStreamChannel')->set($streamChannel);
        $stream->mockMethod('getKey')->set('foo');

        $server = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $server->mockMethod('getId')->set(1);

        $configuration = new CM_Janus_Configuration([$server]);

        $httpApiClient = $this->mockClass('CM_Janus_HttpApiClient')->newInstanceWithoutConstructor();
        $stopStreamMethod = $httpApiClient->mockMethod('stopStream')->set(function (CM_Janus_Server $passedServer, $streamKey) use ($server) {
            $this->assertSame($server, $passedServer);
            $this->assertSame('foo', $streamKey);
        });
        /** @var CM_Janus_HttpApiClient $httpApiClient */

        $janus = new CM_Janus_Service($configuration, $httpApiClient);
        $this->callProtectedMethod($janus, '_stopStream', [$stream]);
        $this->assertSame(1, $stopStreamMethod->getCallCount());
    }
}
