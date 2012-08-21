<?php
require_once __DIR__ . '/../../../../../TestCase.php';

class CM_Paging_StreamChannelArchive_Video_UserTest extends TestCase {

	public function tearDown() {
		TH::clearEnv();
	}

	public function testPaging() {
		$user = TH::createUser();
		TH::createStreamChannelVideoArchive(null, $user);
		TH::createStreamChannelVideoArchive(null, $user);
		TH::timeForward(1);
		$streamChannel3 = TH::createStreamChannelVideoArchive(null, $user);
		TH::createStreamChannelVideoArchive();
		TH::createStreamChannelVideoArchive();
		$paging = new CM_Paging_StreamChannelArchive_Video_User($user);
		$this->assertSame(3, $paging->getCount());
		$this->assertModelEquals($streamChannel3, $paging->getItem(0));
	}
}