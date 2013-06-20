<?php

class CM_Paging_StreamPublish_UserTest extends CMTest_TestCase {

	public function testPaging() {
		$user = CMTest_TH::createUser();
		$streamChannel = CMTest_TH::createStreamChannel();
		CMTest_TH::createStreamPublish($user, $streamChannel);

		$streams = new CM_Paging_StreamPublish_User($user);
		$this->assertEquals(1, $streams->getCount());
	}
}
