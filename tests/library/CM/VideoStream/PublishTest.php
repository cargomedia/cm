<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_VideoStream_PublishTest extends TestCase {

	private static $_configBackup;

	public static function setUpBeforeClass() {
		self::$_configBackup = CM_Config::get();
		$testCase = new self();
		$mock = $testCase->getMock('CM_VideoStreamDelegate');
		CM_Config::get()->CM_StreamChannel->delegates[1] = get_class($mock);
	}

	public static function tearDownAfterClass() {
		CM_Config::set(self::$_configBackup);
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
			'key' => '123123_3', 'name' => '12312fghdadsw123', 'delegateType' => 1));
		$this->assertEquals('12312fghdadsw123', $videoStream->getName());
	}

	public function testCreate() {
		$user = TH::createUser();
		$videoStream = CM_VideoStream_Publish::create(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2', 'name' => '123123qadadsw123', 'delegateType' => 1));
		$this->assertRow(TBL_CM_VIDEOSTREAM_PUBLISH, array('userId' => $user->getId(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2', 'delegateType' => 1));
	}

	public function testDelete() {
		$videoStreamPublish = TH::createVideoStreamPublish();
		$videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->add(array('user' => TH::createUser(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '133123_1'));
		$videoStreamSubscribe = $videoStreamPublish->getStreamChannel()->getVideoStreamSubscribes()->getItem(0);
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

    public function testGetDelegateType() {
        /** @var CM_VideoStream_Publish $videoStreamPublish */
        $videoStreamPublish = CM_VideoStream_Publish::create(array('user' => TH::createUser(), 'start' => 123123, 'allowedUntil' => 324234,
        			'key' => '1323_3', 'name' => '12312fghdadsasdw123', 'delegateType' => 1));
        $this->assertEquals(1, $videoStreamPublish->getDelegateType());
    }
}
