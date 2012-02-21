<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_VideoStream_PublishTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testConstructor() {
		$videoStreamPublish = TH::createVideoStreamPublish();
		$this->assertGreaterThan(0, $videoStreamPublish->getId());
		try {
			new CM_VideoStream_Publish(22123);
			$this->fail('Can instantiate nonexistent VideoStream_Publish');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testSetAllowedUntil() {
		$videoStreamPublish = TH::createVideoStreamPublish();
		$videoStreamPublish->setAllowedUntil(234234);
		$this->assertEquals(234234, $videoStreamPublish->getAllowedUntil());
		$videoStreamPublish->setAllowedUntil(2342367);
		$this->assertEquals(2342367, $videoStreamPublish->getAllowedUntil());
	}

	public function testGetName() {
		/** @var CM_VideoStream_Publish $videoStream */
		$videoStream = CM_VideoStream_Publish::create(array('user' => TH::createUser(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_3', 'name' => '12312fghdadsw123'));
		$this->assertEquals('12312fghdadsw123', $videoStream->getName());
	}

	public function testCreate() {
		$user = TH::createUser();
		$videoStream = CM_VideoStream_Publish::create(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2', 'name' => '123123qadadsw123'));
		$this->assertRow(TBL_CM_VIDEOSTREAM_PUBLISH, array('userId' => $user->getId(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2'));
	}

	public function testDelete() {
		$videoStreamPublish = TH::createVideoStreamPublish();
		$videoStreamPublish->getVideoStreamSubscribes()->add(array('user' => TH::createUser(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '133123_1'));
		$videoStreamSubscribe = $videoStreamPublish->getVideoStreamSubscribes()->getItem(0);
		$videoStreamPublish->delete();
		try {
			new CM_VideoStream_Publish($videoStreamPublish->getId());
			$this->fail('videoStream_publish not deleted.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
		try {
			new CM_VideoStream_Subscribe($videoStreamSubscribe->getId());
			$this->fail('StreamSubscriptions associated with deleted StreamPublish not deleted');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testFindStreamName() {
		$videoStreamPublishOrig = TH::createVideoStreamPublish();
		$videoStreamPublish = CM_VideoStream_Publish::findStreamName($videoStreamPublishOrig->getName());
		$this->assertModelEquals($videoStreamPublish, $videoStreamPublishOrig);
		$videoStreamPublish = CM_VideoStream_Publish::findStreamName('doesnotexist');
		$this->assertNull($videoStreamPublish);
	}

	public function testFindKey() {
		$videoStreamPublishOrig = TH::createVideoStreamPublish();
		$videoStreamPublish = CM_VideoStream_Publish::findKey($videoStreamPublishOrig->getKey());
		$this->assertModelEquals($videoStreamPublish, $videoStreamPublishOrig);
		$videoStreamPublish = CM_VideoStream_Publish::findKey('doesnotexist');
		$this->assertNull($videoStreamPublish);
	}
}
