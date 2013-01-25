<?php

class CM_Model_StreamChannelArchive_VideoTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testCreate() {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = CMTest_TH::createStreamChannel();
		$user = CMTest_TH::createUser();
		$streamPublish = CMTest_TH::createStreamPublish($user, $streamChannel);
		CMTest_TH::timeForward(10);
		/** @var CM_Model_StreamChannelArchive_Video $archive */
		$archive = CM_Model_StreamChannelArchive_Video::create(array('streamChannel' => $streamChannel));
		$this->assertInstanceOf('CM_Model_StreamChannelArchive_Video', $archive);
		$this->assertSame($streamChannel->getId(), $archive->getId());
		$this->assertSame($user->getId(), $archive->getUserId());
		$this->assertModelEquals($user, $archive->getUser());
		$this->assertSame($streamChannel->getWidth(), $archive->getWidth());
		$this->assertSame($streamChannel->getHeight(), $archive->getHeight());
		$this->assertSame($streamPublish->getStart(), $archive->getCreated());
		$this->assertEquals(10, $archive->getDuration(), '', 1);
		$this->assertSame($streamChannel->getThumbnailCount(), $archive->getThumbnailCount());
		$this->assertSame(md5($streamPublish->getKey()), $archive->getHash());
		$this->assertSame($streamChannel->getType(), $archive->getStreamChannelType());

		$streamChannel = CMTest_TH::createStreamChannel();
		try {
			CM_Model_StreamChannelArchive_Video::create(array('streamChannel' => $streamChannel));
			$this->fail('StreamChannelArchive_Video created without StreamPublish.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
	}

	public function testGetUser() {
		$user = CMTest_TH::createUser();
		$streamChannel = CMTest_TH::createStreamChannel();
		CMTest_TH::createStreamPublish($user, $streamChannel);
		$archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel);
		$this->assertModelEquals($user, $archive->getUser());
		$user->delete();
		$this->assertNull($archive->getUser());
	}

	public function testGetVideo() {
		$archive = CMTest_TH::createStreamChannelVideoArchive();
		$videoFile = $archive->getVideo();
		$this->assertSame('streamChannels/' . $archive->getId() . '/' . $archive->getId() . '-' . $archive->getHash() .
				'-original.mp4', $videoFile->getPathRelative());
	}

	public function testGetThumbnails() {
		$archive = CMTest_TH::createStreamChannelVideoArchive();
		$this->assertSame(array(), $archive->getThumbnails()->getItems());

		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamChannel->setThumbnailCount(2);
		$archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel);
		$thumb1 = new CM_File_UserContent('streamChannels', $archive->getId() . '-' . $archive->getHash() . '-thumbs/1.png', $streamChannel->getId());
		$thumb2 = new CM_File_UserContent('streamChannels', $archive->getId() . '-' . $archive->getHash() . '-thumbs/2.png', $streamChannel->getId());
		$this->assertEquals(array($thumb1, $thumb2), $archive->getThumbnails()->getItems());
	}

	public function testOnDelete() {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamChannel->setThumbnailCount(3);
		$archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel);
		$files = $this->_createArchiveFiles($archive);
		foreach ($files as $file) {
			$this->assertFileExists($file->getPath());
		}

		$archive->delete();
		foreach ($files as $file) {
			$this->assertFileNotExists($file->getPath());
		}
		try {
			new CM_Model_StreamChannelArchive_Video($archive->getId());
			$this->fail('StreamChannelArchive not deleted.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testDeleteOlder() {
		$time = time();
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannelsDeleted = array();
		$archivesDeleted = array();
		/** @var $filesDeleted CM_File[] */
		$filesDeleted = array();
		for ($i = 0; $i < 2; $i++) {
			$streamChannel = CMTest_TH::createStreamChannel();
			$streamChannel->setThumbnailCount(4);
			$streamChannelsDeleted[] = $streamChannel;
			$archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel);
			$archivesDeleted[] = $archive;
			$filesDeleted = array_merge($filesDeleted, $this->_createArchiveFiles($archive));
		}

		$streamChannelsNotDeleted = array();
		$archivesNotDeleted = array();
		/** @var $filesNotDeleted CM_File[] */
		$filesNotDeleted = array();
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamChannel = $this->getMock('CM_Model_StreamChannel_Video', array('getType'), array($streamChannel->getId()));
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
			$archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel);
			$archivesNotDeleted[] = $archive;
			$filesNotDeleted = array_merge($filesNotDeleted, $this->_createArchiveFiles($archive));
		}

		foreach ($filesNotDeleted as $file) {
			$this->assertFileExists($file->getPath());
		}
		foreach ($filesDeleted as $file) {
			$this->assertFileExists($file->getPath());
		}
		CM_Model_StreamChannelArchive_Video::deleteOlder(10, CM_Model_StreamChannel_Video::TYPE);
		foreach ($filesNotDeleted as $file) {
			$this->assertFileExists($file->getPath());
		}
		foreach ($filesDeleted as $file) {
			$this->assertFileNotExists($file->getPath());
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

	/**
	 * @param CM_Model_StreamChannelArchive_Video $archive
	 * @return CM_File[]
	 */
	private function _createArchiveFiles(CM_Model_StreamChannelArchive_Video $archive) {
		$thumbPath = DIR_USERFILES . 'streamChannels' . DIRECTORY_SEPARATOR . $archive->getId() . DIRECTORY_SEPARATOR . $archive->getId() . '-' .
				$archive->getHash() . '-thumbs';
		CM_Util::mkDir($thumbPath);
		$files = array();
		for ($i = 0; $i < $archive->getThumbnailCount(); $i++) {
			$file = CM_File::create($archive->getThumbnails()->getItem($i)->getPath());
			$files[] = $file;
		}
		$files[] = CM_File::create($archive->getVideo()->getPath());
		return $files;
	}

}
