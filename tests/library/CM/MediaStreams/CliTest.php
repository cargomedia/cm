<?php

class CM_MediaStreams_CliTest extends CMTest_TestCase {

    public function testImportVideoThumbnail() {
        $testFile1 = CM_File::create('test1.png', 'foo1', CM_Service_Manager::getInstance()->getFilesystems()->getTmp());
        $testFile2 = CM_File::create('test2.png', 'foo2', CM_Service_Manager::getInstance()->getFilesystems()->getTmp());
        $cli = new CM_MediaStreams_Cli();
        // streamchannel exists
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel(null, null, 'foobar');

        $this->assertCount(0, $streamChannel->getThumbnails());
        $cli->importVideoThumbnail('foobar', $testFile1, 1234);
        $this->assertCount(1, $streamChannel->getThumbnails());
        /** @var CM_StreamChannel_Thumbnail $thumbnail */
        $thumbnail = $streamChannel->getThumbnails()->getItem(0);
        $this->assertSame(1234, $thumbnail->getCreateStamp());
        $this->assertSame('foo1', $thumbnail->getFile()->read());

        // archive exists
        $archive = CM_Model_StreamChannelArchive_Media::createStatic(['streamChannel' => $streamChannel]);
        $streamChannel->delete();
        $cli->importVideoThumbnail('foobar', $testFile2, 1235);
        $this->assertCount(2, $streamChannel->getThumbnails());
        /** @var CM_StreamChannel_Thumbnail $thumbnail */
        $thumbnail = $archive->getThumbnails()->getItem(1);
        $this->assertSame(1235, $thumbnail->getCreateStamp());
        $this->assertSame('foo2', $thumbnail->getFile()->read());
    }
}
