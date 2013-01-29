<?php

class CM_StreamAdapter_ApacheTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testPublishSubscribe() {
		$user = CMTest_TH::createUser();
		$channel = CM_Stream::getStreamChannel($user);
		$message = "what up?";

		$streamer = new CM_StreamAdapter_Apache();
		$streamer->publish($channel, $message);

		$result = $streamer->subscribe(CM_Stream::getStreamChannel($user));
		$result = json_decode($result['data']);
		$this->assertSame($message, $result);
	}

	public function testPublishSubscribeArray() {
		$user = CMTest_TH::createUser();
		$channel = CM_Stream::getStreamChannel($user);
		$message = array('my' => 'arrays', 'are' => 'cool');

		$streamer = new CM_StreamAdapter_Apache();
		$streamer->publish($channel, $message);

		$result = $streamer->subscribe($channel);
		$result = json_decode($result['data'], true);
		$this->assertSame($message, $result);
	}
}
