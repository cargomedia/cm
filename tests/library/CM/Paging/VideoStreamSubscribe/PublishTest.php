<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Paging_VideoStreamSubscribe_PublishTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testAdd() {
		$videoStreamPublish = TH::createVideoStreamPublish();
		$user = TH::createUser();
		$this->assertEquals(0, $videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->getCount());
		$videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->add(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2'));
		$this->assertEquals(1, $videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->getCount());
		$this->assertRow(TBL_CM_VIDEOSTREAM_SUBSCRIBE, array('userId' => $user->getId(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2'));
	}

	public function testDelete() {
		$videoStreamPublish = TH::createVideoStreamPublish();
		$user = TH::createUser();
		$videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->add(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123124_1'));
		$videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->add(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123124_2'));
		$this->assertEquals(2, $videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->getCount());
		$videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->delete($videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->getItem(0));
		$this->assertEquals(1, $videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->getCount());

		$videoStreamSubscribe = TH::createVideoStreamSubscribe();
		try {
			$videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->delete($videoStreamSubscribe);
			$this->fail('VideoStream_Publish deleted VideoStreamSubscribe not subscribing to it.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
		try {
			new CM_VideoStream_Subscribe($videoStreamSubscribe->getId());
			$this->assertTrue(true);
		} catch (CM_Exception_Nonexistent $ex) {
			$this->fail('VideoStream_Publish deleted VideoStreamSubscribe not subscribing to it.');
		}
	}
}
