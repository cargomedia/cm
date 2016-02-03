<?php

class CM_MediaStreams_StreamRepositoryTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testConstructor() {
        $repository = new CM_MediaStreams_StreamRepository(1);
        $this->assertInstanceOf('CM_MediaStreams_StreamRepository', $repository);
    }

    public function testCreateStreamChannel() {
        $repository = new CM_MediaStreams_StreamRepository(1);
        $mediaId = '444-bar';
        $channel = $repository->createStreamChannel('foo', CM_Model_StreamChannel_Media::getTypeStatic(), 2, $mediaId);
        $this->assertInstanceOf('CM_Model_StreamChannel_Media', $channel);

        CM_Model_StreamChannelArchive_Media::createStatic(['streamChannel' => $channel]);

        $exception = $this->catchException(function () use ($repository, $mediaId) {
            $repository->createStreamChannel('bar', CM_Model_StreamChannel_Media::getTypeStatic(), 2, $mediaId);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Channel archive with mediaId `' . $mediaId . '` already exists', $exception->getMessage());
    }

    public function testRemoveStreamChannel() {
        $streamSubscribe = $this->mockObject();
        $streamSubscribe->mockMethod('delete');
        $streamPublish = $this->mockObject();
        $streamPublish->mockMethod('delete');

        $streamChannel = $this->mockClass('CM_Model_StreamChannel_Abstract')->newInstanceWithoutConstructor();
        $streamChannel->mockMethod('delete');
        $streamChannel->mockMethod('getStreamSubscribes')->set([$streamSubscribe]);
        $streamChannel->mockMethod('getStreamPublishs')->set([$streamPublish]);

        $streamRepository = new CM_MediaStreams_StreamRepository(1);
        $streamRepository->removeStreamChannel($streamChannel);

        $this->assertSame(1, $streamSubscribe->mockMethod('delete')->getCallCount());
        $this->assertSame(1, $streamPublish->mockMethod('delete')->getCallCount());
        $this->assertSame(1, $streamChannel->mockMethod('delete')->getCallCount());
    }
}
