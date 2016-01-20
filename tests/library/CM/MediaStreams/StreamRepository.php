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
        $channel = $repository->createStreamChannel('foo', CM_Model_StreamChannel_Media::getTypeStatic(), 2, 3, $mediaId);
        $this->assertInstanceOf('CM_Model_StreamChannel_Media', $channel);

        CM_Model_StreamChannelArchive_Media::createStatic(['streamChannel' => $channel]);

        $exception = $this->catchException(function () use ($repository, $mediaId) {
            $repository->createStreamChannel('bar', CM_Model_StreamChannel_Media::getTypeStatic(), 2, 5, $mediaId);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Channel archive with mediaId `' . $mediaId . '` already exists', $exception->getMessage());
    }
}
