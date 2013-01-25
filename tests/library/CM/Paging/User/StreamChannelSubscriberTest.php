<?php

class CM_Paging_User_StreamChannelSubscriberTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {

	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testPaging() {
		$usersExpected = array(CMTest_TH::createUser(), CMTest_TH::createUser(), CMTest_TH::createUser());
		$streamChannel = CMTest_TH::createStreamChannel();

		foreach ($usersExpected as $user) {
			CMTest_TH::createStreamSubscribe($user, $streamChannel);
		}
		CMTest_TH::createStreamSubscribe(null, $streamChannel);
		CMTest_TH::createStreamSubscribe(null, $streamChannel);

		$usersActual = new CM_Paging_User_StreamChannelSubscriber($streamChannel);
		$this->assertEquals(3, $usersActual->getCount());
		$i = 0;
		foreach ($usersActual as $user) {
			$this->assertModelEquals($usersExpected[$i++], $user);
		}
	}
}
