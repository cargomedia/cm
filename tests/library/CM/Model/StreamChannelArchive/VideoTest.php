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
		$archive = CM_Model_StreamChannelArchive_Video::create(array('object' => $streamChannel));
		$this->assertInstanceOf('CM_Model_StreamChannelArchive_Video', $archive);
		$this->assertSame($streamChannel->getId(), $archive->getId());
		$this->assertSame($user->getId(), $archive->getUserId());
		$this->assertModelEquals($user, $archive->getUser());
		$this->assertSame($streamChannel->getWidth(), $archive->getWidth());
		$this->assertSame($streamChannel->getHeight(), $archive->getHeight());
		$this->assertSame($streamPublish->getStart(), $archive->getCreated());
		$this->assertSame(10, $archive->getDuration());
	}
}