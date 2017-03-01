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
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Channel archive with this mediaId already exists', $exception->getMessage());
        $this->assertSame(['mediaId' => $mediaId], $exception->getMetaInfo());
    }

    public function testRemoveStreamChannel() {
        $streamSubscribe = $this->mockClass(CM_Model_Stream_Subscribe::class)->newInstanceWithoutConstructor();
        $streamSubscribe->mockMethod('delete');
        $streamPublish = $this->mockClass(CM_Model_Stream_Publish::class)->newInstanceWithoutConstructor();
        $streamPublish->mockMethod('delete');

        $pagingSubscribes = $this->mockClass('CM_Paging_StreamSubscribe_StreamChannel')->newInstanceWithoutConstructor();
        $pagingSubscribes->mockMethod('getItems')->set([$streamSubscribe]);
        $pagingPublishs = $this->mockClass('CM_Paging_StreamPublish_StreamChannel')->newInstanceWithoutConstructor();
        $pagingPublishs->mockMethod('getItems')->set([$streamPublish]);

        $streamChannel = $this->mockClass('CM_Model_StreamChannel_Abstract')->newInstanceWithoutConstructor();
        $streamChannel->mockMethod('delete');
        $streamChannel->mockMethod('getStreamSubscribes')->set($pagingSubscribes);
        $streamChannel->mockMethod('getStreamPublishs')->set($pagingPublishs);

        $streamRepository = new CM_MediaStreams_StreamRepository(1);
        $streamRepository->removeStreamChannel($streamChannel);

        $this->assertSame(1, $streamSubscribe->getOverrides()->get('delete')->getCallCount());
        $this->assertSame(1, $streamPublish->getOverrides()->get('delete')->getCallCount());
        $this->assertSame(1, $streamChannel->getOverrides()->get('delete')->getCallCount());
    }
}
