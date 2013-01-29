<?php

class CM_Model_Stream_PublishTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testConstructor() {
		$videoStreamPublish = CMTest_TH::createStreamPublish();
		$this->assertGreaterThan(0, $videoStreamPublish->getId());
		try {
			new CM_Model_Stream_Publish(22123);
			$this->fail('Can instantiate nonexistent VideoStream_Publish');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testSetAllowedUntil() {
		$videoStreamPublish = CMTest_TH::createStreamPublish();
		$videoStreamPublish->setAllowedUntil(234234);
		$this->assertEquals(234234, $videoStreamPublish->getAllowedUntil());
		$videoStreamPublish->setAllowedUntil(2342367);
		$this->assertEquals(2342367, $videoStreamPublish->getAllowedUntil());
	}

	public function testCreate() {
		$user = CMTest_TH::createUser();
		$streamChannel = CMTest_TH::createStreamChannel();
		$this->assertEquals(0, $streamChannel->getStreamPublishs()->getCount());
		$videoStream = CM_Model_Stream_Publish::create(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2', 'streamChannel' => $streamChannel));
		$this->assertRow(TBL_CM_STREAM_PUBLISH, array('userId' => $user->getId(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2', 'channelId' => $streamChannel->getId()));
		$this->assertEquals(1, $streamChannel->getStreamPublishs()->getCount());
	}

	public function testDelete() {
		$streamChannel = CMTest_TH::createStreamChannel();
		$videoStreamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
		$this->assertEquals(1, $streamChannel->getStreamPublishs()->getCount());
		$videoStreamPublish->delete();
		try {
			new CM_Model_Stream_Publish($videoStreamPublish->getId());
			$this->fail('videoStream_publish not deleted.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
		$this->assertEquals(0, $streamChannel->getStreamPublishs()->getCount());
	}

	public function testFindKey() {
		$videoStreamPublishOrig = CMTest_TH::createStreamPublish();
		$videoStreamPublish = CM_Model_Stream_Publish::findKey($videoStreamPublishOrig->getKey());
		$this->assertEquals($videoStreamPublish, $videoStreamPublishOrig);
		$videoStreamPublish = CM_Model_Stream_Publish::findKey('doesnotexist');
		$this->assertNull($videoStreamPublish);
	}

	public function testGetChannel() {
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
		$this->assertEquals($streamChannel, $streamPublish->getStreamChannel());

	}
}
