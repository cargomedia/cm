<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Paging_StreamSubscribe_StreamChannelTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testAdd() {
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = TH::createStreamChannel();
		$user = TH::createUser();
		$this->assertEquals(0, $streamChannel->getStreamSubscribes()->getCount());
		$streamChannel->getStreamSubscribes()->add(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2'));
		$this->assertEquals(1, $streamChannel->getStreamSubscribes()->getCount());
		$this->assertRow(TBL_CM_STREAM_SUBSCRIBE, array('userId' => $user->getId(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2'));
	}

	public function testDelete() {
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = TH::createStreamChannel();
		$user = TH::createUser();
		$streamChannel->getStreamSubscribes()->add(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123124_1'));
		$streamChannel->getStreamSubscribes()->add(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123124_2'));
		$this->assertEquals(2, $streamChannel->getStreamSubscribes()->getCount());
		$streamChannel->getStreamSubscribes()->remove($streamChannel->getStreamSubscribes()->getItem(0));
		$this->assertEquals(1, $streamChannel->getStreamSubscribes()->getCount());

		$videoStreamSubscribe = TH::createStreamSubscribe();
		try {
			$streamChannel->getStreamSubscribes()->remove($videoStreamSubscribe);
			$this->fail('StreamChannel deleted StreamSubscribe not belonging to it.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
		try {
			new CM_Model_Stream_Subscribe($videoStreamSubscribe->getId());
			$this->assertTrue(true);
		} catch (CM_Exception_Nonexistent $ex) {
			$this->fail('StreamChannel deleted StreamSubscribe not belonging to it.');
		}
	}
}
