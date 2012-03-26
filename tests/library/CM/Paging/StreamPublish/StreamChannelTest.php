<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Paging_StreamPublish_StreamChannelTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testAdd() {
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = TH::createStreamChannel();
		$user = TH::createUser();
		$this->assertEquals(0, $streamChannel->getStreamPublishs()->getCount());
		$streamChannel->getStreamPublishs()->add(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2'));
		$this->assertEquals(1, $streamChannel->getStreamPublishs()->getCount());
		$this->assertRow(TBL_CM_STREAM_PUBLISH, array('userId' => $user->getId(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2'));
	}

	public function testDelete() {
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = TH::createStreamChannel();
		$user = TH::createUser();
		$streamChannel->getStreamPublishs()->add(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123124_1'));
		$streamChannel->getStreamPublishs()->add(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123124_2'));
		$this->assertEquals(2, $streamChannel->getStreamPublishs()->getCount());
		$streamChannel->getStreamPublishs()->remove($streamChannel->getStreamPublishs()->getItem(0));
		$this->assertEquals(1, $streamChannel->getStreamPublishs()->getCount());

		$streamPublish = TH::createStreamPublish();
		try {
			$streamChannel->getStreamPublishs()->remove($streamPublish);
			$this->fail('StreamChannel deleted StreamPublish not belonging to it.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
		try {
			new CM_Model_Stream_Publish($streamPublish->getId());
			$this->assertTrue(true);
		} catch (CM_Exception_Nonexistent $ex) {
			$this->fail('StreamChannel deleted StreamPublish not belonging to it.');
		}
	}
}
