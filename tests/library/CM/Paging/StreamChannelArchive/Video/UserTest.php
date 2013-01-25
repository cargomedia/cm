<?php

class CM_Paging_StreamChannelArchive_Video_UserTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testPaging() {
		$user = CMTest_TH::createUser();
		CMTest_TH::createStreamChannelVideoArchive(null, $user);
		CMTest_TH::createStreamChannelVideoArchive(null, $user);
		CMTest_TH::timeForward(1);
		$streamChannel3 = CMTest_TH::createStreamChannelVideoArchive(null, $user);
		CMTest_TH::createStreamChannelVideoArchive();
		CMTest_TH::createStreamChannelVideoArchive();
		$paging = new CM_Paging_StreamChannelArchiveVideo_User($user);
		$this->assertSame(3, $paging->getCount());
		$this->assertModelEquals($streamChannel3, $paging->getItem(0));
	}
}
