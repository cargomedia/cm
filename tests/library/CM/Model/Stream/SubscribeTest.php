<?php

class CM_Model_Stream_SubscribeTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
	}

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testConstructor() {
		$id = CM_Model_Stream_Subscribe::create(array('user' => CMTest_TH::createUser(), 'start' => time(), 'allowedUntil' => time() + 100,
			'streamChannel' => CMTest_TH::createStreamChannel(), 'key' => '13215231_1'))->getId();
		$streamSubscribe = new CM_Model_Stream_Subscribe($id);
		$this->assertGreaterThan(0, $streamSubscribe->getId());
		try {
			new CM_Model_Stream_Subscribe(22467);
			$this->fail('Can instantiate nonexistent VideoStream_Subscribe');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testDuplicateKeys() {
		$data = array('user' => CMTest_TH::createUser(), 'start' => time(), 'allowedUntil' => time() + 100,
			'streamChannel' => CMTest_TH::createStreamChannel(), 'key' => '13215231_1');
		CM_Model_Stream_Subscribe::create($data);
		try {
			CM_Model_Stream_Subscribe::create($data);
			$this->fail('Should not be able to create duplicate key instance');
		} catch (CM_Exception $e) {
			$this->assertContains('Duplicate entry', $e->getMessage());
		}
		$data['streamChannel'] = CMTest_TH::createStreamChannel();
		CM_Model_Stream_Subscribe::create($data);
	}

	public function testSetAllowedUntil() {
		$videoStreamSubscribe = CMTest_TH::createStreamSubscribe(CMTest_TH::createUser());
		$videoStreamSubscribe->setAllowedUntil(234234);
		$this->assertSame(234234, $videoStreamSubscribe->getAllowedUntil());
		$videoStreamSubscribe->setAllowedUntil(2342367);
		$this->assertSame(2342367, $videoStreamSubscribe->getAllowedUntil());
		$videoStreamSubscribe->setAllowedUntil(null);
		$this->assertNull($videoStreamSubscribe->getAllowedUntil());
	}

	public function testCreate() {
		$user = CMTest_TH::createUser();
		$streamChannel = CMTest_TH::createStreamChannel();
		$this->assertEquals(0, $streamChannel->getStreamSubscribes()->getCount());
		$videoStream = CM_Model_Stream_Subscribe::create(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'streamChannel' => $streamChannel, 'key' => '123123_2'));
		$this->assertRow(TBL_CM_STREAM_SUBSCRIBE, array('id' => $videoStream->getId(), 'userId' => $user->getId(), 'start' => 123123,
			'allowedUntil' => 324234, 'channelId' => $streamChannel->getId(), 'key' => '123123_2'));
		$this->assertEquals(1, $streamChannel->getStreamSubscribes()->getCount());
	}

	public function testCreateWithoutUser() {
		$streamChannel = CMTest_TH::createStreamChannel();
		$this->assertEquals(0, $streamChannel->getStreamSubscribes()->getCount());
		$videoStream = CM_Model_Stream_Subscribe::create(array('user' => null, 'start' => 123123, 'allowedUntil' => 324234,
			'streamChannel' => $streamChannel, 'key' => '123123_2'));
		$this->assertRow(TBL_CM_STREAM_SUBSCRIBE, array('id' => $videoStream->getId(), 'userId' => null, 'start' => 123123, 'allowedUntil' => 324234,
			'channelId' => $streamChannel->getId(), 'key' => '123123_2'));
		$this->assertEquals(1, $streamChannel->getStreamSubscribes()->getCount());
	}

	public function testGetUser() {
		$streamChannel = CMTest_TH::createStreamChannel();
		/** @var CM_Model_Stream_Subscribe $streamSubscribeWithoutUser */
		$streamSubscribeWithoutUser = CM_Model_Stream_Subscribe::create(array('user' => null, 'start' => 123123, 'allowedUntil' => 324234,
			'streamChannel' => $streamChannel, 'key' => '123123_2'));
		$this->assertNull($streamSubscribeWithoutUser->getUser());
		$this->assertNull($streamSubscribeWithoutUser->getUserId());

		$user = CMTest_TH::createUser();
		/** @var CM_Model_Stream_Subscribe $streamSubscribe */
		$streamSubscribe = CM_Model_Stream_Subscribe::create(array('user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'streamChannel' => $streamChannel, 'key' => '123123_3'));
		$this->assertEquals($user, $streamSubscribe->getUser());
		$this->assertSame($user->getId(), $streamSubscribe->getUserId());
	}

	public function testDelete() {
		$streamChannel = CMTest_TH::createStreamChannel();
		$videoStreamSubscribe = CM_Model_Stream_Subscribe::create(array('user' => CMTest_TH::createUser(), 'start' => time(),
			'allowedUntil' => time() + 100, 'streamChannel' => $streamChannel, 'key' => '13215231_2'));
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

	public function testFindByKeyAndChannel() {
		$videoStreamSubscribeOrig = CMTest_TH::createStreamSubscribe(CMTest_TH::createUser());
		$videoStreamSubscribe = CM_Model_Stream_Subscribe::findByKeyAndChannel($videoStreamSubscribeOrig->getKey(), $videoStreamSubscribeOrig->getStreamChannel());
		$this->assertEquals($videoStreamSubscribe, $videoStreamSubscribeOrig);
	}

	public function testFindKeyNonexistent() {
		$streamChannel = CMTest_TH::createStreamChannel();
		$videoStreamSubscribe = CM_Model_Stream_Subscribe::findByKeyAndChannel('doesnotexist', $streamChannel);
		$this->assertNull($videoStreamSubscribe);
	}

	public function testGetKey() {
		$user = CMTest_TH::createUser();
		$streamChannel = CMTest_TH::createStreamChannel();
		/** @var CM_Model_Stream_Subscribe $streamSubscribe */
		$streamSubscribe = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => time(),
			'allowedUntil' => time() + 100, 'key' => 'foo'));
		$this->assertSame('foo', $streamSubscribe->getKey());
	}

	public function testGetKeyMaxLength() {
		$user = CMTest_TH::createUser();
		$streamChannel = CMTest_TH::createStreamChannel();
		/** @var CM_Model_Stream_Subscribe $streamSubscribe */
		$streamSubscribe = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => time(),
			'allowedUntil' => time() + 100, 'key' => str_repeat('a', 100)));
		$this->assertSame(str_repeat('a', 36), $streamSubscribe->getKey());
	}

	public function testGetChannel() {
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamPublish = CMTest_TH::createStreamSubscribe(CMTest_TH::createUser(), $streamChannel);
		$this->assertEquals($streamChannel, $streamPublish->getStreamChannel());

	}
}
