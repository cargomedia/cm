<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Paging_StreamPublish_UserTest extends TestCase {

	public static function setUpBeforeClass() {

	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testPaging() {
		$user = TH::createUser();
		$streamChannel = TH::createStreamChannel();
		TH::createStreamPublish($user, $streamChannel);

		$streams = new CM_Paging_StreamPublish_User($user);
		$this->assertEquals(1, $streams->getCount());

		$user->delete();
		$streams = new CM_Paging_StreamPublish_User($user);
		$this->assertEquals(0, $streams->getCount());
	}
}