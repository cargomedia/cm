<?php

class CM_Model_StreamChannel_MediaTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        /** @var CM_Model_StreamChannel_Media $channel1 */
        $channel1 = CM_Model_StreamChannel_Media::createStatic(array(
            'key'            => 'foo',
            'serverId'       => 1,
            'thumbnailCount' => 2,
            'adapterType'    => 1,
        ));
        $this->assertInstanceOf('CM_Model_StreamChannel_Media', $channel1);
        $this->assertSame('foo', $channel1->getKey());
        $this->assertSame(1, $channel1->getAdapterType());
        $this->assertSame(2, $channel1->getThumbnailCount());
        $this->assertSame(null, $channel1->getMediaId());
        $this->assertSame(time(), $channel1->getCreateStamp());

        /** @var CM_Model_StreamChannel_Media $channel */
        $channel2 = CM_Model_StreamChannel_Media::createStatic(array(
            'key'            => 'bar',
            'serverId'       => 1,
            'thumbnailCount' => 2,
            'adapterType'    => 1,
            'mediaId'        => 'foobar',
        ));
        $this->assertSame('foobar', $channel2->getMediaId());

        $channel3 = CM_Model_StreamChannel_Media::createStatic(array(
            'key'            => 'foobar',
            'serverId'       => 1,
            'thumbnailCount' => 2,
            'adapterType'    => 1,
            'mediaId'        => null,
        ));
        $this->assertSame(null, $channel3->getMediaId());
    }

    public function testCreateWithoutServerId() {
        try {
            CM_Model_StreamChannel_Media::createStatic(array(
                'key'            => 'bar',
                'serverId'       => null,
                'thumbnailCount' => 2,
                'adapterType'    => 1,
            ));
            $this->fail('Can create streamChannel without serverId');
        } catch (CM_Exception $ex) {
            $this->assertContains("Column 'serverId' cannot be null", $ex->getMessage());
        }
    }

    public function testGetStreamPublish() {
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        try {
            $streamChannel->getStreamPublish();
            $this->fail();
        } catch (CM_Exception_Invalid $ex) {
            $this->assertContains('has no StreamPublish.', $ex->getMessage());
        }
        $streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
        $this->assertEquals($streamPublish, $streamChannel->getStreamPublish());
    }

    public function testHasStreamPublish() {
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        $this->assertFalse($streamChannel->hasStreamPublish());
        CMTest_TH::createStreamPublish(null, $streamChannel);
        $this->assertTrue($streamChannel->hasStreamPublish());
    }

    public function testThumbnailCount() {
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        $streamChannel->setThumbnailCount(15);
        $this->assertSame(15, $streamChannel->getThumbnailCount());
    }

    public function testOnDelete() {
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        $streamChannel->delete();
        $exception = $this->catchException(function () use ($streamChannel) {
            CMTest_TH::reinstantiateModel($streamChannel);
        });
        $this->assertInstanceOf('CM_Exception_Nonexistent', $exception);
        $this->assertInstanceOf('CM_Model_StreamChannelArchive_Media', CM_Model_StreamChannelArchive_Media::findById($streamChannel->getId()));
    }

    public function testOnUnpublish() {
        $streamChannel = CMTest_TH::createStreamChannel();
        $streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
        $this->assertNull(CM_Model_StreamChannelArchive_Media::findById($streamChannel->getId()));

        $streamChannel->onUnpublish($streamPublish);
        $this->assertInstanceOf('CM_Model_StreamChannelArchive_Media', CM_Model_StreamChannelArchive_Media::findById($streamChannel->getId()));

        $streamChannel->onUnpublish($streamPublish);
        $streamPublish->delete();
        $streamChannel->delete();
    }

    public function testOnUnpublishDelete() {
        $streamChannel = CMTest_TH::createStreamChannel();
        $streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
        try {
            $streamChannel->onUnpublish($streamPublish);
            new CM_Model_StreamChannelArchive_Media($streamChannel->getId());
            $streamPublish->delete();
        } catch (CM_Exception_Nonexistent $ex) {
            $this->fail('Could not delete CM_Model_Stream_Publish.');
        }
    }

    public function testGetHash() {
        // with streamPublish
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        $streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
        $this->assertSame(md5($streamPublish->getKey()), $streamChannel->getHash());

        // without streamPublish
        $streamChannel = CMTest_TH::createStreamChannel();
        $this->assertSame(md5($streamChannel->getKey()), $streamChannel->getHash());
    }

    public function testGetThumbnails() {
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        CMTest_TH::createStreamPublish(null, $streamChannel);
        $this->assertSame(array(), $streamChannel->getThumbnails()->getItems());
        $streamChannel->setThumbnailCount(2);
        $thumb1 = new CM_File_UserContent('streamChannels',
            $streamChannel->getId() . '-' . $streamChannel->getHash() . '-thumbs/1.png', $streamChannel->getId());
        $thumb2 = new CM_File_UserContent('streamChannels',
            $streamChannel->getId() . '-' . $streamChannel->getHash() . '-thumbs/2.png', $streamChannel->getId());
        $this->assertEquals(array($thumb1, $thumb2), $streamChannel->getThumbnails()->getItems());
    }

    public function testGetThumbnail() {
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        CMTest_TH::createStreamPublish(null, $streamChannel);
        $thumbnail = $streamChannel->getThumbnail(3);
        $this->assertInstanceOf('CM_File_UserContent', $thumbnail);
        $this->assertSame(
            'streamChannels/' . $streamChannel->getId() . '/' . $streamChannel->getId() . '-' . $streamChannel->getHash() . '-thumbs/3.png',
            $thumbnail->getPathRelative());
    }

    public function testFindByMediaId() {
        $this->assertNull(CM_Model_StreamChannelArchive_Media::findByMediaId('foo'));
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = $streamChannel = CMTest_TH::createStreamChannel(null, null, 'foo');
        $this->assertEquals($streamChannel, CM_Model_StreamChannel_Media::findByMediaId($streamChannel->getMediaId()));
    }
}

