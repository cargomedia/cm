<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Paging_User_StreamChannelSubscriberTest extends TestCase {

	public static function setUpBeforeClass() {

	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testPaging() {
		$usersExpected = array(TH::createUser(), TH::createUser(), TH::createUser());
		$streamChannel = TH::createStreamChannel();

		foreach ($usersExpected as $user) {
			TH::createStreamSubscribe($user, $streamChannel);
		}
		TH::createStreamSubscribe(null, $streamChannel);
		TH::createStreamSubscribe(null, $streamChannel);

		$usersActual = new CM_Paging_User_StreamChannelSubscriber($streamChannel);
		$this->assertEquals(3, $usersActual->getCount());
		$i = 0;
		foreach ($usersActual as $user) {
			$this->assertModelEquals($usersExpected[$i++], $user);
		}
	}
}
