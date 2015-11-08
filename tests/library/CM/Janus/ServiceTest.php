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
                        ['streamKey' => 'stream-foo', 'streamChannelKey' => 'channel-foo', 'isPublish' => false],
                    ];
                case 5:
                    return [
                        ['streamKey' => 'stream-bar', 'streamChannelKey' => 'channel-bar', 'isPublish' => false],
                        ['streamKey' => 'stream-zoo', 'streamChannelKey' => 'channel-zoo', 'isPublish' => false],
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
        $stopStreamMethod = $httpApiClient->mockMethod('stopStream')
            ->at(0, function (CM_Janus_Server $passedServer, $streamKey) use ($server) {
                $this->assertSame($server, $passedServer);
                $this->assertSame('foo', $streamKey);
                return ['success' => true];
            })
            ->at(1, function (CM_Janus_Server $passedServer, $streamKey) use ($server) {
                return ['error' => 'Cannot stop stream'];
            });
        /** @var CM_Janus_HttpApiClient $httpApiClient */

        $janus = new CM_Janus_Service($configuration, $httpApiClient);
        $this->callProtectedMethod($janus, '_stopStream', [$stream]);
        $this->assertSame(1, $stopStreamMethod->getCallCount());

        $exception = $this->catchException(function () use ($janus, $stream) {
            $this->callProtectedMethod($janus, '_stopStream', [$stream]);
        });
        $this->assertTrue($exception instanceof CM_Janus_StopStreamError);
        $this->assertSame(2, $stopStreamMethod->getCallCount());
    }

    public function testSynchronizeMissingInJanus() {
        $streamChannel = CMTest_TH::createStreamChannel(null, CM_Janus_Service::getTypeStatic());
        $streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
        $streamSubscribe = CMTest_TH::createStreamSubscribe(null, $streamChannel);

        $server1 = $this->mockClass('CM_Janus_Server')->newInstance([1, 'key', 'http://mock', 'ws://mock']);
        /** @var CM_Janus_Configuration|\Mocka\AbstractClassTrait $configuration */
        $configuration = $this->mockObject('CM_Janus_Configuration');
        $configuration->mockMethod('getServers')->set([$server1]);

        /** @var CM_Janus_HttpApiClient|\Mocka\AbstractClassTrait $httpApiClient */
        $httpApiClient = $this->mockClass('CM_Janus_HttpApiClient')->newInstanceWithoutConstructor();
        $httpApiClient->mockMethod('fetchStatus')->set([]);

        $janus = new CM_Janus_Service($configuration, $httpApiClient);
        $janus->synchronize();

        $this->assertEquals($streamChannel, CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamChannel->getKey(), $janus->getType()));
        $this->assertEquals($streamPublish, CM_Model_Stream_Publish::findByKeyAndChannel($streamPublish->getKey(), $streamChannel));
        $this->assertEquals($streamSubscribe, CM_Model_Stream_Subscribe::findByKeyAndChannel($streamSubscribe->getKey(), $streamChannel));

        CMTest_TH::timeForward(5);
        $janus->synchronize();

        $this->assertNull(CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamChannel->getKey(), $janus->getType()));
        $this->assertNull(CM_Model_Stream_Publish::findByKeyAndChannel($streamPublish->getKey(), $streamChannel));
        $this->assertNull(CM_Model_Stream_Subscribe::findByKeyAndChannel($streamSubscribe->getKey(), $streamChannel));
    }

    public function testSynchronizeMissingInPhp() {
        $streamChannel = CMTest_TH::createStreamChannel(null, CM_Janus_Service::getTypeStatic());
        $streamChannelKey = $streamChannel->getKey();
        $streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
        $streamPublishKey = $streamPublish->getKey();
        $streamSubscribe = CMTest_TH::createStreamSubscribe(null, $streamChannel);
        $streamSubscribeKey = $streamSubscribe->getKey();

        $server1 = $this->mockClass('CM_Janus_Server')->newInstance([1, 'key', 'http://mock', 'ws://mock']);
        /** @var CM_Janus_Configuration|\Mocka\AbstractClassTrait $configuration */
        $configuration = $this->mockObject('CM_Janus_Configuration');
        $configuration->mockMethod('getServers')->set([$server1]);

        $status = [
            ['streamKey' => $streamPublishKey, 'streamChannelKey' => $streamChannelKey, 'isPublish' => true],
            ['streamKey' => $streamSubscribeKey, 'streamChannelKey' => $streamChannelKey, 'isPublish' => false],
        ];

        $httpApiClient = $this->mockClass('CM_Janus_HttpApiClient')->newInstanceWithoutConstructor();
        $httpApiClient->mockMethod('fetchStatus')->set(function (CM_Janus_Server $passedServer) use ($server1, $status) {
            $this->assertSame($server1, $passedServer);
            return $status;
        });

        $stopStreamMethod = $httpApiClient->mockMethod('stopStream')
            ->at(0, function ($server, $streamKey) use ($streamPublishKey, $server1) {
                $this->assertEquals($server1, $server);
                $this->assertSame($streamPublishKey, $streamKey);
            })
            ->at(1, function ($server, $streamKey) use ($streamSubscribeKey, $server1) {
                $this->assertEquals($server1, $server);
                $this->assertSame($streamSubscribeKey, $streamKey);
            });
        /** @var CM_Janus_HttpApiClient $httpApiClient */

        $janus = new CM_Janus_Service($configuration, $httpApiClient);
        $janus->getStreamRepository()->removeStream($streamPublish);
        $janus->getStreamRepository()->removeStream($streamSubscribe);
        $janus->synchronize();
        $this->assertSame(2, $stopStreamMethod->getCallCount());
    }
}
