<?php

class CM_Model_StreamChannelArchive_MediaTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        // with streamPublish
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        $user = CMTest_TH::createUser();
        $streamPublish = CMTest_TH::createStreamPublish($user, $streamChannel);
        CMTest_TH::timeForward(10);
        /** @var CM_Model_StreamChannelArchive_Media $archive */
        $archive = CM_Model_StreamChannelArchive_Media::createStatic(array('streamChannel' => $streamChannel));
        $this->assertInstanceOf('CM_Model_StreamChannelArchive_Media', $archive);
        $this->assertSame($streamChannel->getId(), $archive->getId());
        $this->assertSame($user->getId(), $archive->getUserId());
        $this->assertEquals($user, $archive->getUser());
        $this->assertSame($streamPublish->getStart(), $archive->getCreated());
        $this->assertEquals(10, $archive->getDuration(), '', 1);
        $this->assertSame($streamChannel->getThumbnailCount(), $archive->getThumbnailCount());
        $this->assertSame(md5($streamChannel->getKey()), $archive->getHash());
        $this->assertSame($streamChannel->getType(), $archive->getStreamChannelType());
        $this->assertSame($streamChannel->getKey(), $archive->getKey());
        $this->assertSame($streamChannel->getMediaId(), $archive->getMediaId());
        $this->assertSame($streamChannel->getHash(), $archive->getHash());

        // without streamPublish
        $streamChannel = CMTest_TH::createStreamChannel(null, null, 'foo');
        $archive = CM_Model_StreamChannelArchive_Media::createStatic(array('streamChannel' => $streamChannel));
        $this->assertSame($streamChannel->getMediaId(), $archive->getMediaId());
        $this->assertSame($streamChannel->getCreateStamp(), $archive->getCreated());
        $this->assertSame($streamChannel->getHash(), $archive->getHash());
    }

    public function testCreateDuplicated() {
        $channel = CM_Model_StreamChannel_Media::createStatic(array(
            'key'            => 'foo',
            'serverId'       => 1,
            'thumbnailCount' => 2,
            'adapterType'    => 1,
            'mediaId'        => 'foo',
        ));
        $archive1 = CM_Model_StreamChannelArchive_Media::createStatic(['streamChannel' => $channel]);
        $archive2 = CM_Model_StreamChannelArchive_Media::createStatic(['streamChannel' => $channel]);
        $this->assertInstanceOf('CM_Model_StreamChannelArchive_Media', $archive2);

        $this->assertSame($archive1->getId(), $archive2->getId());
        $this->assertSame($archive1->getCreated(), $archive2->getCreated());
    }

    public function testNoUser() {
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        $user = CMTest_TH::createUser();
        $streamPublish = CMTest_TH::createStreamPublish($user, $streamChannel);
        $streamPublish->unsetUser();

        /** @var CM_Model_StreamChannelArchive_Media $archive */
        $archive = CM_Model_StreamChannelArchive_Media::createStatic(array('streamChannel' => $streamChannel));

        $this->assertNull($archive->getUser());
        $this->assertNull($archive->getUserId());
    }

    public function testGetUser() {
        $user = CMTest_TH::createUser();
        $streamChannel = CMTest_TH::createStreamChannel();
        CMTest_TH::createStreamPublish($user, $streamChannel);
        $archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel);
        $this->assertEquals($user, $archive->getUser());
        $user->delete();
        $this->assertNull($archive->getUser());
    }

    public function testGetVideo() {
        $filename = 'testArchive.mp4';
        $archive = CMTest_TH::createStreamChannelVideoArchive(null, null, $filename);
        $videoFile = $archive->getFile();

        $this->assertSame('streamChannels/' . $archive->getId() . '/' . $filename, $videoFile->getPathRelative());
    }

    public function testGetNullFile() {
        $archive = CMTest_TH::createStreamChannelVideoArchive();
        $this->assertFalse($archive->hasFile());
        $exception = $this->catchException(function () use ($archive) {
            $archive->getFile();
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('File does not exist', $exception->getMessage());

        $archive = CMTest_TH::createStreamChannelVideoArchive(null, null, 'archive.mp4');
        $this->assertTrue($archive->hasFile());
        $this->assertNotEmpty($archive->getFile());

        $archive->setFile(null);
        $this->assertFalse($archive->hasFile());
        $exception = $this->catchException(function () use ($archive) {
            $archive->getFile();
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('File does not exist', $exception->getMessage());
    }

    public function testGetThumbnails() {
        $archive = CMTest_TH::createStreamChannelVideoArchive();
        $this->assertSame(array(), $archive->getThumbnails()->getItems());

        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        $streamChannel->setThumbnailCount(2);
        $archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel);
        $thumb1 = new CM_File_UserContent('streamChannels', $archive->getId() . '-' . $archive->getHash() . '-thumbs/1.png', $streamChannel->getId());
        $thumb2 = new CM_File_UserContent('streamChannels', $archive->getId() . '-' . $archive->getHash() . '-thumbs/2.png', $streamChannel->getId());
        $this->assertEquals(array($thumb1, $thumb2), $archive->getThumbnails()->getItems());
    }

    public function testGetThumbnail() {
        $archive = CMTest_TH::createStreamChannelVideoArchive();
        $thumbnail = $archive->getThumbnail(3);
        $this->assertInstanceOf('CM_File_UserContent', $thumbnail);
        $this->assertSame(
            'streamChannels/' . $archive->getId() . '/' . $archive->getId() . '-' . $archive->getHash() . '-thumbs/3.png',
            $thumbnail->getPathRelative());
    }

    public function testOnDelete() {
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        $streamChannel->setThumbnailCount(3);
        $archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel, null, 'tempFileName.mp4');
        $files = $this->_createArchiveFiles($archive);
        foreach ($files as $file) {
            $this->assertTrue($file->exists());
        }

        $archive->delete();
        foreach ($files as $file) {
            $this->assertFalse($file->exists());
        }
        try {
            new CM_Model_StreamChannelArchive_Media($archive->getId());
            $this->fail('StreamChannelArchive not deleted.');
        } catch (CM_Exception_Nonexistent $ex) {
            $this->assertTrue(true);
        }
    }

    public function testDeleteOlder() {
        $time = time();
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannelsDeleted = array();
        $archivesDeleted = array();
        /** @var $filesDeleted CM_File[] */
        $filesDeleted = array();
        for ($i = 0; $i < 2; $i++) {
            $streamChannel = CMTest_TH::createStreamChannel();
            $streamChannel->setThumbnailCount(4);
            $streamChannelsDeleted[] = $streamChannel;
            $archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel, null, 'tempFileName.mp4');
            $archivesDeleted[] = $archive;
            $filesDeleted = array_merge($filesDeleted, $this->_createArchiveFiles($archive));
        }

        $streamChannelsNotDeleted = array();
        $archivesNotDeleted = array();
        /** @var $filesNotDeleted CM_File[] */
        $filesNotDeleted = array();
        $streamChannel = CMTest_TH::createStreamChannel();
        $streamChannel = $this->getMock('CM_Model_StreamChannel_Media', array('getType'), array($streamChannel->getId()));
        $streamChannel->expects($this->any())->method('getType')->will($this->returnValue(3));
        $streamChannelsNotDeleted[] = $streamChannel;
        $archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel);
        $archivesNotDeleted[] = $archive;
        $filesNotDeleted = array_merge($filesNotDeleted, $this->_createArchiveFiles($archive));

        CMTest_TH::timeForward(20);
        for ($i = 0; $i < 3; $i++) {
            $streamChannel = CMTest_TH::createStreamChannel();
            $streamChannel->setThumbnailCount(4);
            $streamChannelsNotDeleted[] = $streamChannel;
            $archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel, null, 'tempFileName.mp4');
            $archivesNotDeleted[] = $archive;
            $filesNotDeleted = array_merge($filesNotDeleted, $this->_createArchiveFiles($archive));
        }

        foreach ($filesNotDeleted as $file) {
            $this->assertTrue($file->exists());
        }
        foreach ($filesDeleted as $file) {
            $this->assertTrue($file->exists());
        }
        CM_Model_StreamChannelArchive_Media::deleteOlder(10, CM_Model_StreamChannel_Media::getTypeStatic());
        foreach ($filesNotDeleted as $file) {
            $this->assertTrue($file->exists());
        }
        foreach ($filesDeleted as $file) {
            $this->assertFalse($file->exists());
        }
        foreach ($archivesNotDeleted as $archive) {
            try {
                CMTest_TH::reinstantiateModel($archive);
                $this->assertTrue(true);
            } catch (CM_Exception_Nonexistent $ex) {
                $this->fail('Young streamchannelArchive deleted');
            }
        }
        foreach ($archivesDeleted as $archive) {
            try {
                CMTest_TH::reinstantiateModel($archive);
                $this->fail('Old streamchannelArchive not deleted');
            } catch (CM_Exception_Nonexistent $ex) {
                $this->assertTrue(true);
            }
        }
    }

    public function testFindById() {
        $streamChannel = $streamChannel = CMTest_TH::createStreamChannel();
        $this->assertNull(CM_Model_StreamChannelArchive_Media::findById($streamChannel->getId()));

        CMTest_TH::createStreamPublish(null, $streamChannel);
        CM_Model_StreamChannelArchive_Media::createStatic(array('streamChannel' => $streamChannel));
        $this->assertInstanceOf('CM_Model_StreamChannelArchive_Media', CM_Model_StreamChannelArchive_Media::findById($streamChannel->getId()));
    }

    public function testFindByMediaId() {
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = $streamChannel = CMTest_TH::createStreamChannel(null, null, 'foo');
        $this->assertNull(CM_Model_StreamChannelArchive_Media::findByMediaId($streamChannel->getMediaId()));

        $streamChannelArchive = CM_Model_StreamChannelArchive_Media::createStatic(['streamChannel' => $streamChannel]);
        $this->assertEquals($streamChannelArchive, CM_Model_StreamChannelArchive_Media::findByMediaId($streamChannel->getMediaId()));
    }

    public function testSetThumbnailCount() {
        $streamChannelArchive = CMTest_TH::createStreamChannelVideoArchive();
        $this->assertSame(0, $streamChannelArchive->getThumbnailCount());
        $streamChannelArchive->setThumbnailCount(5);
        $this->assertSame(5, $streamChannelArchive->getThumbnailCount());
    }

    /**
     * @param CM_Model_StreamChannelArchive_Media $archive
     * @return CM_File[]
     */
    private function _createArchiveFiles(CM_Model_StreamChannelArchive_Media $archive) {
        $files = array();
        if ($archive->getThumbnailCount() > 0) {
            /** @var CM_File_UserContent $thumbnailFirst */
            $thumbnailFirst = $archive->getThumbnails()->getItem(0);
            $thumbnailFirst->ensureParentDirectory();
            $files[] = $thumbnailFirst->getParentDirectory();
        }
        for ($i = 0; $i < $archive->getThumbnailCount(); $i++) {
            /** @var CM_File_UserContent $file */
            $file = $archive->getThumbnails()->getItem($i);
            $file->write('');
            $files[] = $file;
        }
        if ($archive->hasFile()) {
            $video = $archive->getFile();
            $video->ensureParentDirectory();
            $video->write('');
            $files[] = $video;
        }
        return $files;
    }
}
