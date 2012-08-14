<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_StreamChannelArchive_VideoTest extends TestCase {

	public function tearDown() {
		TH::clearEnv();
	}

	public function testCreate() {
		$user = TH::createUser();
		/** @var CM_Model_StreamChannelArchive_Video $archive */
		$archive = CM_Model_StreamChannelArchive_Video::create(array('id' => 12, 'userId' => $user->getId(), 'width' => 640, 'height' => 480, 'start' => 56, 'end' => 100));
		$this->assertInstanceOf('CM_Model_StreamChannelArchive_Video', $archive);
		$this->assertSame(12, $archive->getId());
		$this->assertSame($user->getId(), $archive->getUserId());
		$this->assertModelEquals($user, $archive->getUser());
		$this->assertSame(640, $archive->getWidth());
		$this->assertSame(480, $archive->getHeight());
		$this->assertSame(56, $archive->getCreated());
		$this->assertSame(44, $archive->getDuration());
	}
}