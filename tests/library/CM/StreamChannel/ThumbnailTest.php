<?php

class CM_StreamChannel_ThumbnailTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $thumbnail = CM_StreamChannel_Thumbnail::create(1, 123);
        $this->assertSame(1, $thumbnail->getChannelId());
        $this->assertSame(123, $thumbnail->getCreateStamp());
    }

    public function testGetHash() {
        $thumbnail = CM_StreamChannel_Thumbnail::create(1, 123);
        $this->assertSame(md5(1 . '-' . $thumbnail->getId()), $thumbnail->getHash());
    }

    public function testGetFile() {
        $thumbnail = CM_StreamChannel_Thumbnail::create(1, 123);
        $expected = new CM_File_UserContent('streamChannels', 1 . '-thumbs' . DIRECTORY_SEPARATOR . $thumbnail->getCreateStamp() . '-' .
            $thumbnail->getHash() . '-' . $thumbnail->getId(), 1);
        $this->assertEquals($expected, $thumbnail->getFile());
    }

    public function testDelete() {
        $thumbnail = CM_StreamChannel_Thumbnail::create(1, 123);
        $file = $thumbnail->getFile();
        $file->ensureParentDirectory();
        $file->write('foo');

        $thumbnail->delete();
        $this->assertTrue($file->exists()); // file only deleted by batch-delete on archive-delete
    }
}
