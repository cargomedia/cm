<?php

class CM_StreamChannel_ThumbnailList_ChannelTest extends CMTest_TestCase {

    public function testPaging() {
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        $archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel);
        $this->assertEquals([], $archive->getThumbnails());

        $thumbnail1 = CM_StreamChannel_Thumbnail::create($archive->getId(), 2);
        $thumbnail2 = CM_StreamChannel_Thumbnail::create($archive->getId(), 1);
        $this->assertEquals([$thumbnail2, $thumbnail1], $archive->getThumbnails());
        $this->assertEquals([$thumbnail2, $thumbnail1], $streamChannel->getThumbnails());

        $thumbnail2->delete();
        $this->assertEquals([$thumbnail1], $archive->getThumbnails());
        $this->assertEquals([$thumbnail1], $streamChannel->getThumbnails());
        $thumbnail1->delete();
        $this->assertEquals([], $archive->getThumbnails());
        $this->assertEquals([], $streamChannel->getThumbnails());
    }
}
