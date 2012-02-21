<?php
require_once __DIR__ . '/../../../TestCase.php';

class SK_VideoStream_SubscribeTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testConstructor() {
		$id = CM_VideoStream_Subscribe::create(array('user' => TH::createUser(), 'start' => time(), 'allowedUntil' => time() + 100,
			'publish' => TH::createVideoStreamPublish(), 'key' => '13215231_1'))->getId();
		$streamSubscribe = new CM_VideoStream_Subscribe($id);
		$this->assertGreaterThan(0, $streamSubscribe->getId());
		try {
			new CM_VideoStream_Subscribe(22467);
			$this->fail('Can instantiate nonexistent VideoStream_Subscribe');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testSetAllowedUntil() {
		$videoStreamSubscribe = TH::createVideoStreamSubscribe();
		$videoStreamSubscribe->setAllowedUntil(234234);
		$this->assertEquals(234234, $videoStreamSubscribe->getAllowedUntil());
		$videoStreamSubscribe->setAllowedUntil(2342367);
		$this->assertEquals(2342367, $videoStreamSubscribe->getAllowedUntil());
	}

	public function testCreate() {
		$user = TH::createUser();
		$videoStreamPublish = TH::createVideoStreamPublish();
		$videoStream = CM_VideoStream_Subscribe::create(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'publish' => $videoStreamPublish, 'key' => '123123_2'));
		$this->assertRow(TBL_CM_VIDEOSTREAM_SUBSCRIBE, array('id' => $videoStream->getId(), 'userId' => $user->getId(), 'start' => 123123,
			'allowedUntil' => 324234, 'publishId' => $videoStreamPublish->getId(), 'key' => '123123_2'));
	}

	public function testDelete() {
		$videoStreamSubscribe = CM_VideoStream_Subscribe::create(array('user' => TH::createUser(), 'start' => time(), 'allowedUntil' => time() + 100,
			'publish' => TH::createVideoStreamPublish(), 'key' => '13215231_2'));
		$videoStreamSubscribe->delete();
		try {
			new CM_VideoStream_Subscribe($videoStreamSubscribe->getId());
			$this->fail('videoStream_susbcribe not deleted.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testFindKey() {
		$videoStreamSubscribeOrig = TH::createVideoStreamSubscribe();
		$videoStreamSubscribe = CM_VideoStream_Subscribe::findKey($videoStreamSubscribeOrig->getKey());
		$this->assertModelEquals($videoStreamSubscribe, $videoStreamSubscribeOrig);
		$videoStreamSubscribe = CM_VideoStream_Subscribe::findKey('doesnotexist');
		$this->assertNull($videoStreamSubscribe);
	}
}
