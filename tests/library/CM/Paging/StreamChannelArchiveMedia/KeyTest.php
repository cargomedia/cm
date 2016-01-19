<?php

class CM_Paging_StreamChannelArchiveMedia_KeyTest extends CMTest_TestCase {

    public function testPaging() {
        $key = 'foo';
        $streamChannel1 = CMTest_TH::createStreamChannel();
        $streamChannel1->_set('key', $key);
        $streamChannel2 = CMTest_TH::createStreamChannel();
        $streamChannel2->_set('key', $key);

        $this->assertEquals([], new CM_Paging_StreamChannelArchiveMedia_Key($key));
        $streamChannelArchive1 = CMTest_TH::createStreamChannelVideoArchive($streamChannel1);
        CMTest_TH::timeForward(1);
        $streamChannelArchive2 = CMTest_TH::createStreamChannelVideoArchive($streamChannel2);
        $this->assertEquals([$streamChannelArchive2, $streamChannelArchive1], new CM_Paging_StreamChannelArchiveMedia_Key($key));
    }
}
