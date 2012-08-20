<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_StreamChannelArchive_VideoTest extends TestCase {

	public function tearDown() {
		TH::clearEnv();
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
}