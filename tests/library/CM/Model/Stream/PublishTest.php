<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_Stream_PublishTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testConstructor() {
		$videoStreamPublish = TH::createStreamPublish();
		$this->assertGreaterThan(0, $videoStreamPublish->getId());
		try {
			new CM_Model_Stream_Publish(22123);
			$this->fail('Can instantiate nonexistent VideoStream_Publish');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testSetAllowedUntil() {
		$videoStreamPublish = TH::createStreamPublish();
		$videoStreamPublish->setAllowedUntil(234234);
		$this->assertEquals(234234, $videoStreamPublish->getAllowedUntil());
		$videoStreamPublish->setAllowedUntil(2342367);
		$this->assertEquals(2342367, $videoStreamPublish->getAllowedUntil());
	}

	public function testCreate() {
		$user = TH::createUser();
		$streamChannel = TH::createStreamChannel();
		$videoStream = CM_Model_Stream_Publish::create(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2', 'streamChannel' => $streamChannel));
		$this->assertRow(TBL_CM_STREAM_PUBLISH, array('userId' => $user->getId(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2', 'channelId' => $streamChannel->getId()));
	}

	public function testDelete() {
		$videoStreamPublish = TH::createStreamPublish();
		$videoStreamPublish->delete();
		try {
			new CM_Model_Stream_Publish($videoStreamPublish->getId());
			$this->fail('videoStream_publish not deleted.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testFindKey() {
		$videoStreamPublishOrig = TH::createStreamPublish();
		$videoStreamPublish = CM_Model_Stream_Publish::findKey($videoStreamPublishOrig->getKey());
		$this->assertModelEquals($videoStreamPublish, $videoStreamPublishOrig);
		$videoStreamPublish = CM_Model_Stream_Publish::findKey('doesnotexist');
		$this->assertNull($videoStreamPublish);
	}

	public function testGetChannel() {
		$streamChannel = TH::createStreamChannel();
		$streamPublish = TH::createStreamPublish(null, $streamChannel);
		$this->assertModelEquals($streamChannel, $streamPublish->getStreamChannel());

	}
}
