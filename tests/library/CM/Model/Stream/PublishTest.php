<?php

class CM_Model_Stream_PublishTest extends CMTest_TestCase {

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

	public function testDuplicateKeys() {
		$data = array('user'          => CMTest_TH::createUser(), 'start' => time(), 'allowedUntil' => time() + 100,
					  'streamChannel' => CMTest_TH::createStreamChannel(), 'key' => '13215231_1');
		CM_Model_Stream_Publish::create($data);
		try {
			CM_Model_Stream_Publish::create($data);
			$this->fail('Should not be able to create duplicate key instance');
		} catch (CM_Exception $e) {
			$this->assertContains('Duplicate entry', $e->getMessage());
		}
		$data['streamChannel'] = CMTest_TH::createStreamChannel();
		CM_Model_Stream_Publish::create($data);
	}

	public function testSetAllowedUntil() {
		$videoStreamPublish = CMTest_TH::createStreamPublish();
		$videoStreamPublish->setAllowedUntil(234234);
		$this->assertSame(234234, $videoStreamPublish->getAllowedUntil());
		$videoStreamPublish->setAllowedUntil(2342367);
		$this->assertSame(2342367, $videoStreamPublish->getAllowedUntil());
		$videoStreamPublish->setAllowedUntil(null);
		$this->assertNull($videoStreamPublish->getAllowedUntil());
	}

	public function testCreate() {
		$user = CMTest_TH::createUser();
		$streamChannel = CMTest_TH::createStreamChannel();
		$this->assertEquals(0, $streamChannel->getStreamPublishs()->getCount());
		$videoStream = CM_Model_Stream_Publish::create(array('user'          => $user, 'start' => 123123, 'allowedUntil' => 324234,
															 'key'           => '123123_2',
															 'streamChannel' => $streamChannel));
		$this->assertRow(TBL_CM_STREAM_PUBLISH, array('userId'    => $user->getId(), 'start' => 123123, 'allowedUntil' => 324234, 'key' => '123123_2',
													  'channelId' => $streamChannel->getId()));
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
		$videoStreamPublish = CM_Model_Stream_Publish::findByKeyAndChannel($videoStreamPublishOrig->getKey(), $videoStreamPublishOrig->getStreamChannel());
		$this->assertEquals($videoStreamPublish, $videoStreamPublishOrig);
	}

	public function testFindKeyNonexistent() {
		$streamChannel = CMTest_TH::createStreamChannel();
		$videoStreamPublish = CM_Model_Stream_Publish::findByKeyAndChannel('doesnotexist', $streamChannel);
		$this->assertNull($videoStreamPublish);
	}

	public function testGetKey() {
		$user = CMTest_TH::createUser();
		$streamChannel = CMTest_TH::createStreamChannel();
		/** @var CM_Model_Stream_Publish $streamPublish */
		$streamPublish = CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => time(),
															   'allowedUntil'  => time() + 100,
															   'key'           => 'foo'));
		$this->assertSame('foo', $streamPublish->getKey());
	}

	public function testGetKeyMaxLength() {
		$user = CMTest_TH::createUser();
		$streamChannel = CMTest_TH::createStreamChannel();
		/** @var CM_Model_Stream_Publish $streamPublish */
		$streamPublish = CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => time(),
															   'allowedUntil'  => time() + 100,
															   'key'           => str_repeat('a', 100)));
		$this->assertSame(str_repeat('a', 36), $streamPublish->getKey());
	}

	public function testGetChannel() {
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
		$this->assertEquals($streamChannel, $streamPublish->getStreamChannel());
	}

	public function testUnsetUser() {
		$user = CMTest_TH::createUser();
		$streamChannel = CMTest_TH::createStreamChannel();
		/** @var CM_Model_Stream_Publish $streamPublish */
		$streamPublish = CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => time(),
															   'allowedUntil'  => time() + 100,
															   'key'           => str_repeat('a', 100)));
		$this->assertEquals($user, $streamPublish->getUser());

		$streamPublish->unsetUser();
		$this->assertNull($streamPublish->getUser());
	}
}
