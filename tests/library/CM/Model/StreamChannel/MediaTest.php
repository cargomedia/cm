<?php

class CM_Model_StreamChannel_MediaTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        /** @var CM_Model_StreamChannel_Media $channel1 */
        $channel1 = CM_Model_StreamChannel_Media::createStatic(array(
            'key'         => 'foo',
            'serverId'    => 1,
            'adapterType' => 1,
        ));
        $this->assertInstanceOf('CM_Model_StreamChannel_Media', $channel1);
        $this->assertSame('foo', $channel1->getKey());
        $this->assertSame(1, $channel1->getAdapterType());
        $this->assertSame(null, $channel1->getMediaId());
        $this->assertSame(time(), $channel1->getCreateStamp());

        /** @var CM_Model_StreamChannel_Media $channel */
        $channel2 = CM_Model_StreamChannel_Media::createStatic(array(
            'key'         => 'bar',
            'serverId'    => 1,
            'adapterType' => 1,
            'mediaId'     => 'foobar',
        ));
        $this->assertSame('foobar', $channel2->getMediaId());

        $channel3 = CM_Model_StreamChannel_Media::createStatic(array(
            'key'         => 'foobar',
            'serverId'    => 1,
            'adapterType' => 1,
            'mediaId'     => null,
        ));
        $this->assertSame(null, $channel3->getMediaId());
    }

    public function testCreateWithExistingArchiveMedia() {
        $channel1 = CM_Model_StreamChannel_Media::createStatic(array(
            'key'            => 'foo',
            'serverId'       => 1,
            'thumbnailCount' => 2,
            'adapterType'    => 1,
            'mediaId'        => 'foo',
        ));
        $archive = CM_Model_StreamChannelArchive_Media::createStatic(['streamChannel' => $channel1]);
        $this->assertInstanceOf('CM_Model_StreamChannelArchive_Media', $archive);
        $this->assertSame($channel1->getMediaId(), $archive->getMediaId());

        $exception = $this->catchException(function () {
            CM_Model_StreamChannel_Media::createStatic(array(
                'key'            => 'baz',
                'serverId'       => 1,
                'thumbnailCount' => 3,
                'adapterType'    => 1,
                'mediaId'        => 'foo',
            ));
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Channel archive with given mediaId already exists', $exception->getMessage());
    }

    public function testCreateWithoutServerId() {
        try {
            CM_Model_StreamChannel_Media::createStatic(array(
                'key'         => 'bar',
                'serverId'    => null,
                'adapterType' => 1,
            ));
            $this->fail('Can create streamChannel without serverId');
        } catch (CM_Exception $ex) {
            $this->assertContains("Column 'serverId' cannot be null", $ex->getMetaInfo()['originalExceptionMessage']);
        }
    }

    public function testCreateDuplicate() {
        /** @var CM_Model_StreamChannel_Media $channel1 */
        $channel1 = CM_Model_StreamChannel_Media::createStatic(array(
            'key'         => 'foo',
            'serverId'    => 1,
            'adapterType' => 1,
        ));

        /** @var CM_Model_StreamChannel_Media $channel2 */
        $channel2 = CM_Model_StreamChannel_Media::createStatic(array(
            'key'         => 'foo',
            'serverId'    => 1,
            'adapterType' => 1,
        ));

        $this->assertInstanceOf('CM_Model_StreamChannel_Media', $channel1);
        $this->assertInstanceOf('CM_Model_StreamChannel_Media', $channel2);
        $this->assertSame($channel1->getId(), $channel2->getId());
        $this->assertSame($channel1->getCreateStamp(), $channel2->getCreateStamp());
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

    public function testGetHash() {
        $streamChannel = CMTest_TH::createStreamChannel();
        $this->assertSame(md5($streamChannel->getKey()), $streamChannel->getHash());
    }

    public function testFindByMediaId() {
        $this->assertNull(CM_Model_StreamChannelArchive_Media::findByMediaId('foo'));
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = $streamChannel = CMTest_TH::createStreamChannel(null, null, 'foo');
        $this->assertEquals($streamChannel, CM_Model_StreamChannel_Media::findByMediaId($streamChannel->getMediaId()));
    }
}

