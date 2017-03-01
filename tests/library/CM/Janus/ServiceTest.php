<?php

class CM_Janus_ServiceTest extends CMTest_TestCase {

    public function testFetchStatus() {
        $serverList = new CM_Janus_ServerList();
        foreach ([1, 5] as $serverId) {
            $server = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
            $server->mockMethod('getId')->set($serverId);
            /** @var CM_Janus_Server $server */
            $serverList->addServer($server);
        }
        $httpApiClient = $this->mockClass('CM_Janus_HttpApiClient')->newInstanceWithoutConstructor();
        $httpApiClient->mockMethod('fetchStatus')->set(function (CM_Janus_Server $server) {
            switch ($server->getId()) {
                case 1:
                    return [
                        ['id' => 'stream-foo', 'channelName' => 'channel-foo', 'isPublish' => false],
                    ];
                case 5:
                    return [
                        ['id' => 'stream-bar', 'channelName' => 'channel-bar', 'isPublish' => false],
                        ['id' => 'stream-zoo', 'channelName' => 'channel-zoo', 'isPublish' => false],
                    ];
            }
        });
        /** @var CM_Janus_HttpApiClient $httpApiClient */

        $janus = new CM_Janus_Service($serverList, $httpApiClient);
        /** @var CM_Janus_Stream[] $streams */
        $streams = $this->callProtectedMethod($janus, '_fetchStatus');
        $this->assertContainsOnlyInstancesOf('CM_Janus_Stream', $streams);
        $this->assertCount(3, $streams);

        $this->assertSame($serverList->getById(1), $streams[0]->getServer());
        $this->assertSame('stream-foo', $streams[0]->getStreamKey());
        $this->assertSame('channel-foo', $streams[0]->getStreamChannelKey());

        $this->assertSame($serverList->getById(5), $streams[1]->getServer());
        $this->assertSame('stream-bar', $streams[1]->getStreamKey());
        $this->assertSame('channel-bar', $streams[1]->getStreamChannelKey());

        $this->assertSame($serverList->getById(5), $streams[2]->getServer());
        $this->assertSame('stream-zoo', $streams[2]->getStreamKey());
        $this->assertSame('channel-zoo', $streams[2]->getStreamChannelKey());
    }

    public function testStopStream() {
        $streamChannel = $this->mockClass('CM_Model_StreamChannel_Media')->newInstanceWithoutConstructor();
        $streamChannel->mockMethod('getServerId')->set(1);

        $stream = $this->mockClass('CM_Model_Stream_Abstract')->newInstanceWithoutConstructor();
        $stream->mockMethod('getStreamChannel')->set($streamChannel);
        $stream->mockMethod('getKey')->set('foo');

        $server = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $server->mockMethod('getId')->set(1);

        $serverList = new CM_Janus_ServerList([$server]);

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

        $janus = new CM_Janus_Service($serverList, $httpApiClient);
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

        $existingStreamChannel = CMTest_TH::createStreamChannel(null, CM_Janus_Service::getTypeStatic());
        $existingStreamPublish = CMTest_TH::createStreamPublish(null, $existingStreamChannel);
        $existingStreamSubscribe = CMTest_TH::createStreamSubscribe(null, $existingStreamChannel);

        $emptyStreamChannel = CMTest_TH::createStreamChannel(null, CM_Janus_Service::getTypeStatic());

        $location = $this->mockClass('CM_Geo_Point')->newInstanceWithoutConstructor();
        $server1 = $this->mockClass('CM_Janus_Server')->newInstance([1, 'key', 'http://mock', 'ws://mock', [], $location]);
        /** @var CM_Janus_ServerList|\Mocka\AbstractClassTrait $serverList */
        $serverList = $this->mockObject('CM_Janus_ServerList');
        $serverList->mockMethod('getAll')->set([$server1]);

        /** @var CM_Janus_HttpApiClient|\Mocka\AbstractClassTrait $httpApiClient */
        $httpApiClient = $this->mockClass('CM_Janus_HttpApiClient')->newInstanceWithoutConstructor();
        $httpApiClient->mockMethod('fetchStatus')->set([
            [
                'id'          => $existingStreamPublish->getKey(),
                'channelName' => $existingStreamChannel->getKey(),
                'isPublish'   => true,
            ],
            [
                'id'          => $existingStreamSubscribe->getKey(),
                'channelName' => $existingStreamChannel->getKey(),
                'isPublish'   => false,
            ],
        ]);

        $janus = new CM_Janus_Service($serverList, $httpApiClient);
        $janus->synchronize();

        $this->assertEquals($streamChannel, CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamChannel->getKey(), $janus->getType()));
        $this->assertEquals($streamPublish, CM_Model_Stream_Publish::findByKeyAndChannel($streamPublish->getKey(), $streamChannel));
        $this->assertEquals($streamSubscribe, CM_Model_Stream_Subscribe::findByKeyAndChannel($streamSubscribe->getKey(), $streamChannel));

        $this->assertEquals($existingStreamChannel, CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($existingStreamChannel->getKey(), $janus->getType()));
        $this->assertEquals($existingStreamPublish, CM_Model_Stream_Publish::findByKeyAndChannel($existingStreamPublish->getKey(), $existingStreamChannel));
        $this->assertEquals($existingStreamSubscribe, CM_Model_Stream_Subscribe::findByKeyAndChannel($existingStreamSubscribe->getKey(), $existingStreamChannel));

        $this->assertNull(CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($emptyStreamChannel->getKey(), $janus->getType()));

        CMTest_TH::timeForward(5);
        $janus->synchronize();

        $this->assertNull(CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamChannel->getKey(), $janus->getType()));
        $this->assertNull(CM_Model_Stream_Publish::findByKeyAndChannel($streamPublish->getKey(), $streamChannel));
        $this->assertNull(CM_Model_Stream_Subscribe::findByKeyAndChannel($streamSubscribe->getKey(), $streamChannel));

        $this->assertEquals($existingStreamChannel, CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($existingStreamChannel->getKey(), $janus->getType()));
        $this->assertEquals($existingStreamPublish, CM_Model_Stream_Publish::findByKeyAndChannel($existingStreamPublish->getKey(), $existingStreamChannel));
        $this->assertEquals($existingStreamSubscribe, CM_Model_Stream_Subscribe::findByKeyAndChannel($existingStreamSubscribe->getKey(), $existingStreamChannel));
    }

    public function testSynchronizeMissingInPhp() {
        $streamChannel = CMTest_TH::createStreamChannel(null, CM_Janus_Service::getTypeStatic());
        $streamChannelKey = $streamChannel->getKey();
        $streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
        $streamPublishKey = $streamPublish->getKey();
        $streamSubscribe = CMTest_TH::createStreamSubscribe(null, $streamChannel);
        $streamSubscribeKey = $streamSubscribe->getKey();

        $streamChannel2 = CMTest_TH::createStreamChannel(null, CM_Janus_Service::getTypeStatic());
        $streamChannelKey2 = $streamChannel2->getKey();
        CMTest_TH::createStreamPublish(null, $streamChannel2); //to make channel non empty

        $location = $this->mockClass('CM_Geo_Point')->newInstanceWithoutConstructor();
        $server1 = $this->mockClass('CM_Janus_Server')->newInstance([1, 'key', 'http://mock', 'ws://mock', [], $location]);
        /** @var CM_Janus_ServerList|\Mocka\AbstractClassTrait $serverList */
        $serverList = $this->mockObject('CM_Janus_ServerList');
        $serverList->mockMethod('getAll')->set([$server1]);

        $status = [
            ['id' => $streamPublishKey, 'channelName' => $streamChannelKey, 'isPublish' => true],
            ['id' => $streamSubscribeKey, 'channelName' => $streamChannelKey, 'isPublish' => false],
            ['id' => 'absentStreamKey', 'channelName' => 'absentChannelKey', 'isPublish' => false],
            ['id' => 'absentStreamKey2', 'channelName' => $streamChannelKey2, 'isPublish' => true],
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
            })
            ->at(2, function ($server, $streamKey) use ($server1) {
                $this->assertEquals($server1, $server);
                $this->assertSame('absentStreamKey', $streamKey);
            })
            ->at(3, function ($server, $streamKey) use ($server1) {
                $this->assertEquals($server1, $server);
                $this->assertSame('absentStreamKey2', $streamKey);
            });
        /** @var CM_Janus_HttpApiClient $httpApiClient */

        $janus = new CM_Janus_Service($serverList, $httpApiClient);
        $janus->getStreamRepository()->removeStream($streamPublish);
        $janus->getStreamRepository()->removeStream($streamSubscribe);
        $janus->synchronize();
        $this->assertSame(4, $stopStreamMethod->getCallCount());
    }
}
