<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_StreamChannelArchive_VideoTest extends TestCase {

	public function tearDown() {
		TH::clearEnv();
	}

	public static function tearDownAfterClass() {
		$streamChannelPath = DIR_USERFILES . 'streamChannels/';
		CM_Util::rmDir($streamChannelPath);
	}

	public function testCreate() {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = TH::createStreamChannel();
		$user = TH::createUser();
		$streamPublish = TH::createStreamPublish($user, $streamChannel);
		TH::timeForward(10);
		/** @var CM_Model_StreamChannelArchive_Video $archive */
		$archive = CM_Model_StreamChannelArchive_Video::create(array('streamChannel' => $streamChannel));
		$this->assertInstanceOf('CM_Model_StreamChannelArchive_Video', $archive);
		$this->assertSame($streamChannel->getId(), $archive->getId());
		$this->assertSame($user->getId(), $archive->getUserId());
		$this->assertModelEquals($user, $archive->getUser());
		$this->assertSame($streamChannel->getWidth(), $archive->getWidth());
		$this->assertSame($streamChannel->getHeight(), $archive->getHeight());
		$this->assertSame($streamPublish->getStart(), $archive->getCreated());
		$this->assertSame(10, $archive->getDuration());
		$this->assertSame($streamChannel->getThumbnailCount(), $archive->getThumbnailCount());
		$this->assertSame(md5($streamPublish->getKey()), $archive->getHash());

		$streamChannel = TH::createStreamChannel();
		try {
			CM_Model_StreamChannelArchive_Video::create(array('streamChannel' => $streamChannel));
			$this->fail('StreamChannelArchive_Video created without StreamPublish.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
	}

	public function testGetUser() {
		$user = TH::createUser();
		$streamChannel = TH::createStreamChannel();
		TH::createStreamPublish($user, $streamChannel);
		$archive = TH::createStreamChannelVideoArchive($streamChannel);
		$this->assertModelEquals($user, $archive->getUser());
		$user->delete();
		$this->assertNull($archive->getUser());
	}

	public function testGetVideo() {
		$archive = TH::createStreamChannelVideoArchive();
		$videoFile = $archive->getVideo();
		$this->assertSame('streamChannels/' . $archive->getId() . '/' . $archive->getId() . '-' . $archive->getHash() .
				'-original.mp4', $videoFile->getPathRelative());
	}

	public function testGetThumbnails() {
		$archive = TH::createStreamChannelVideoArchive();
		$this->assertSame(array(), $archive->getThumbnails()->getItems());

		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = TH::createStreamChannel();
		$streamChannel->setThumbnailCount(2);
		$archive = TH::createStreamChannelVideoArchive($streamChannel);
		$thumb1 = new CM_File_UserContent('streamChannels', $archive->getId() . '-' . $archive->getHash() . '-thumbs/1.jpg', $streamChannel->getId());
		$thumb2 = new CM_File_UserContent('streamChannels', $archive->getId() . '-' . $archive->getHash() . '-thumbs/2.jpg', $streamChannel->getId());
		$this->assertEquals(array($thumb1, $thumb2), $archive->getThumbnails()->getItems());
	}

	public function testOnDelete() {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = TH::createStreamChannel();
		$streamChannel->setThumbnailCount(3);
		$archive = TH::createStreamChannelVideoArchive($streamChannel);
		$thumbPath = DIR_USERFILES . 'streamChannels' . DIRECTORY_SEPARATOR . $archive->getId() . DIRECTORY_SEPARATOR . $archive->getId() . '-' . $archive->getHash() . '-thumbs';
		CM_Util::mkDir($thumbPath);
		$thumbs = array();
		for ($i = 0; $i < $archive->getThumbnailCount(); $i++) {
			$file = CM_File::create($archive->getThumbnails()->getItem($i)->getPath());
			$this->assertTrue(file_exists($file->getPath()));
		}
		CM_File::create($archive->getVideo()->getPath());
		$this->assertTrue(file_exists($archive->getVideo()->getPath()));
		$this->assertTrue(file_exists($thumbPath));
		$archive->delete();
		$this->assertFalse(file_exists($archive->getVideo()->getPath()));
		$this->assertFalse(file_exists($thumbPath));
		for ($i = 0; $i < $archive->getThumbnailCount(); $i++) {
			$this->assertFalse(file_exists($file->getPath()));
		}
		try {
			new CM_Model_StreamChannelArchive_Video($archive->getId());
			$this->fail('StreamChannelArchive not deleted.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}


}