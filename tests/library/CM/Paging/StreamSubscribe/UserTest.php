<?php

class CM_Paging_StreamSubscribe_UserTest extends CMTest_TestCase {

	public function testPaging() {
		$user = CMTest_TH::createUser();
		$streamChannel = CMTest_TH::createStreamChannel();
		CMTest_TH::createStreamSubscribe($user, $streamChannel);

		$streams = new CM_Paging_StreamSubscribe_User($user);
		$this->assertEquals(1, $streams->getCount());

		$user->delete();
		$streams = new CM_Paging_StreamSubscribe_User($user);
		$this->assertEquals(1, $streams->getCount());
	}
}
