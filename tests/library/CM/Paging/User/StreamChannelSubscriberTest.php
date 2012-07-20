<?php
require_once __DIR__ . '/../../../../TestCase.php';


class CM_Paging_User_StreamChannelSubscriberTest extends TestCase {

	public static function setUpBeforeClass() {

	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testPaging() {
		$streamChannel = TH::createStreamChannel();
		TH::createStreamSubscribe(TH::createUser(), $streamChannel);
		TH::createStreamSubscribe(TH::createUser(), $streamChannel);
		TH::createStreamSubscribe(TH::createUser(), $streamChannel);
		TH::createStreamSubscribe(null, $streamChannel);
		TH::createStreamSubscribe(null, $streamChannel);

		$users = new CM_Paging_User_StreamChannelSubscriber($streamChannel);

		$this->assertEquals(3, $users->getCount());
	}
}