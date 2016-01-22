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

    public function testOnDelete() {
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();

        $archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel, null, 'tempFileName.mp4');
        CM_StreamChannel_Thumbnail::create($archive->getId(), 1);
        CM_StreamChannel_Thumbnail::create($archive->getId(), 1);
        CM_StreamChannel_Thumbnail::create($archive->getId(), 1);
        $this->_createArchiveFiles($archive);
        /** @var CM_File_UserContent[] $thumbnailFiles */
        $thumbnailFiles = \Functional\map($archive->getThumbnails(), function (CM_StreamChannel_Thumbnail $thumbnail) {
            return $thumbnail->getFile();
        });
        $thumbnailDir = $thumbnailFiles[0]->getParentDirectory();
        $mediaFile = $archive->getFile();

        $this->assertCount(3, $archive->getThumbnails());
        foreach ($thumbnailFiles as $file) {
            $this->assertTrue($file->exists());
        }
        $this->assertTrue($thumbnailDir->exists());
        $this->assertTrue($mediaFile->exists());
        $archive->delete();
        foreach ($thumbnailFiles as $file) {
            $this->assertFalse($file->exists());
        }
        $this->assertFalse($thumbnailDir->exists());
        $this->assertFalse($mediaFile->exists());
        $this->assertCount(0, $archive->getThumbnails());
        $exception = $this->catchException(function () use ($archive) {
            CMTest_TH::reinstantiateModel($archive);
        });
        $this->assertInstanceOf('CM_Exception_Nonexistent', $exception);
    }

    public function testDeleteOlder() {
        $archivesDeleted = [];
        $archivesNotDeleted = [];
        for ($i = 0; $i < 2; $i++) {
            $archive = CMTest_TH::createStreamChannelVideoArchive();
            $archivesDeleted[] = $archive;
        }

        $streamChannel = CMTest_TH::createStreamChannel();
        $streamChannel = $this->getMock('CM_Model_StreamChannel_Media', array('getType'), array($streamChannel->getId()));
        $streamChannel->expects($this->any())->method('getType')->will($this->returnValue(3));
        $archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel);
        $archivesNotDeleted[] = $archive;

        CMTest_TH::timeForward(20);
        for ($i = 0; $i < 3; $i++) {
            $archive = CMTest_TH::createStreamChannelVideoArchive();
            $archivesNotDeleted[] = $archive;
        }
        $this->assertCount(6, new CM_Paging_StreamChannelArchiveMedia_All());

        CM_Model_StreamChannelArchive_Media::deleteOlder(10, CM_Model_StreamChannel_Media::getTypeStatic());
        $this->assertCount(4, new CM_Paging_StreamChannelArchiveMedia_All());
        foreach ($archivesNotDeleted as $archive) {
            $exception = $this->catchException(function() use ($archive) {
                CMTest_TH::reinstantiateModel($archive);
            });
            $this->assertNull($exception, 'Deleted archive that was too young or the wrong type');
        }
        foreach ($archivesDeleted as $archive) {
            $exception = $this->catchException(function() use ($archive) {
                CMTest_TH::reinstantiateModel($archive);
            });
            $this->assertInstanceOf('CM_Exception_Nonexistent', $exception, 'Didn\'t delete old archive');
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

    /**
     * @param CM_Model_StreamChannelArchive_Media $archive
     */
    private function _createArchiveFiles(CM_Model_StreamChannelArchive_Media $archive) {
        $files = array();
        /** @var CM_StreamChannel_Thumbnail $thumbnail */
        foreach ($archive->getThumbnails() as $index => $thumbnail) {
            if ($index === 0) {
                $thumbnail->getFile()->ensureParentDirectory();
            }
            $thumbnail->getFile()->write('');
        }
        if ($archive->hasFile()) {
            $video = $archive->getFile();
            $video->ensureParentDirectory();
            $video->write('');
            $files[] = $video;
        }
    }
}
