<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_Stream_SubscribeTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testConstructor() {
		$id = CM_Model_Stream_Subscribe::create(array('user' => TH::createUser(), 'start' => time(), 'allowedUntil' => time() + 100,
			'streamChannel' => TH::createStreamChannel(), 'key' => '13215231_1'))->getId();
		$streamSubscribe = new CM_Model_Stream_Subscribe($id);
		$this->assertGreaterThan(0, $streamSubscribe->getId());
		try {
			new CM_Model_Stream_Subscribe(22467);
			$this->fail('Can instantiate nonexistent VideoStream_Subscribe');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testSetAllowedUntil() {
		$videoStreamSubscribe = TH::createStreamSubscribe();
		$videoStreamSubscribe->setAllowedUntil(234234);
		$this->assertEquals(234234, $videoStreamSubscribe->getAllowedUntil());
		$videoStreamSubscribe->setAllowedUntil(2342367);
		$this->assertEquals(2342367, $videoStreamSubscribe->getAllowedUntil());
	}

	public function testCreate() {
		$user = TH::createUser();
		$streamChannel = TH::createStreamChannel();
		$this->assertEquals(0, $streamChannel->getStreamSubscribes()->getCount());
		$videoStream = CM_Model_Stream_Subscribe::create(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'streamChannel' => $streamChannel, 'key' => '123123_2'));
		$this->assertRow(TBL_CM_STREAM_SUBSCRIBE, array('id' => $videoStream->getId(), 'userId' => $user->getId(), 'start' => 123123,
			'allowedUntil' => 324234, 'channelId' => $streamChannel->getId(), 'key' => '123123_2'));
		$this->assertEquals(1, $streamChannel->getStreamSubscribes()->getCount());
	}

	public function testDelete() {
		$streamChannel = TH::createStreamChannel();
		$videoStreamSubscribe = CM_Model_Stream_Subscribe::create(array('user' => TH::createUser(), 'start' => time(), 'allowedUntil' => time() + 100,
			'streamChannel' => $streamChannel, 'key' => '13215231_2'));
		$this->assertEquals(1, $streamChannel->getStreamSubscribes()->getCount());
		$videoStreamSubscribe->delete();
		try {
			new CM_Model_Stream_Subscribe($videoStreamSubscribe->getId());
			$this->fail('videoStream_susbcribe not deleted.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
		$this->assertEquals(0, $streamChannel->getStreamSubscribes()->getCount());
	}

	public function testFindKey() {
		$videoStreamSubscribeOrig = TH::createStreamSubscribe();
		$videoStreamSubscribe = CM_Model_Stream_Subscribe::findKey($videoStreamSubscribeOrig->getKey());
		$this->assertModelEquals($videoStreamSubscribe, $videoStreamSubscribeOrig);
		$videoStreamSubscribe = CM_Model_Stream_Subscribe::findKey('doesnotexist');
		$this->assertNull($videoStreamSubscribe);
	}

	public function testGetChannel() {
		$streamChannel = TH::createStreamChannel();
		$streamPublish = TH::createStreamSubscribe(null, $streamChannel);
		$this->assertModelEquals($streamChannel, $streamPublish->getStreamChannel());

	}
}
