<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_WowzaTest extends TestCase {

	public function setUp() {
		CM_Config::get()->CM_Wowza->servers = array(1 => array('publicHost' => 'wowza1.fuckbook.cat.cargomedia', 'publicIp' => '10.0.3.109',
			'privateIp' => '10.0.3.108'));
	}

	public function tearDown() {
		TH::clearEnv();
	}

	public function testSynchronize() {
		/** @var CM_Wowza $wowza */
		$wowza = $this->getMock('CM_Wowza', array('fetchStatus', 'stop'));
		$streamChannels = array();
		$streamChannel = TH::createStreamChannel();
		$streamChannels[] = $streamChannel;
		TH::createStreamPublish(null, $streamChannel);
		TH::createStreamSubscribe(TH::createUser(), $streamChannel);
		TH::createStreamSubscribe(TH::createUser(), $streamChannel);
		TH::createStreamSubscribe(TH::createUser(), $streamChannel);
		$streamChannel1 = TH::createStreamChannel();
		$streamChannels[] = $streamChannel1;
		TH::createStreamPublish(null, $streamChannel1);
		TH::createStreamSubscribe(TH::createUser(), $streamChannel1);
		$streamChannel = TH::createStreamChannel();
		$streamChannels[] = $streamChannel;
		$streamPublishToBeAdded = TH::createStreamPublish(null, $streamChannel);
		$streamSubscribeToBeAdded1 = TH::createStreamSubscribe(TH::createUser(), $streamChannel);
		$streamSubscribeToBeAdded2 = TH::createStreamSubscribe(TH::createUser(), $streamChannel);
		$json = $this->_generateWowzaData($streamChannels);
		$wowza->expects($this->any())->method('fetchStatus')->will($this->returnValue($json));
		$streamChannelToBeAdded = clone($streamChannel);
		$streamChannel->delete();
		$streamSubscribeToBeRemoved3 = TH::createStreamSubscribe(TH::createUser(), $streamChannel1);
		try {
			new CM_Model_StreamChannel_Video($streamChannelToBeAdded->getId());
			$this->fail();
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
		$streamChannelToBeRemoved = TH::createStreamChannel();
		$streamPublishToBeRemoved = TH::createStreamPublish(null, $streamChannelToBeRemoved);
		$streamSubscribeToBeRemoved1 = TH::createStreamSubscribe(TH::createUser(), $streamChannelToBeRemoved);
		$streamSubscribeToBeRemoved2 = TH::createStreamSubscribe(TH::createUser(), $streamChannelToBeRemoved);

		$wowza->synchronize();

		//stuff that should have been added
		$this->assertNotNull($streamChannelAdded = CM_Model_StreamChannel_Abstract::findKey($streamChannelToBeAdded->getKey()));
		$this->assertNotNull($streamPublishAdded = CM_Model_Stream_Publish::findKey($streamPublishToBeAdded->getKey()));
		$this->assertNotNull($streamSubscribeAdded1 = CM_Model_Stream_Subscribe::findKey($streamSubscribeToBeAdded1->getKey()));
		$this->assertNotNull($streamSubscribeAdded2 = CM_Model_Stream_Subscribe::findKey($streamSubscribeToBeAdded2->getKey()));
		$this->assertTrue($streamChannelAdded->getStreamPublishs()->contains($streamPublishAdded));
		$this->assertTrue($streamChannelAdded->getStreamSubscribes()->contains($streamSubscribeAdded1));
		$this->assertTrue($streamChannelAdded->getStreamSubscribes()->contains($streamSubscribeAdded2));
		$this->assertModelEquals($streamPublishToBeAdded->getUser(), $streamPublishAdded->getUser());
		$this->assertModelEquals($streamSubscribeToBeAdded1->getUser(), $streamSubscribeAdded1->getUser());
		$this->assertModelEquals($streamSubscribeToBeAdded2->getUser(), $streamSubscribeAdded2->getUser());
		$this->assertEquals(2, $streamChannelAdded->getThumbnailCount());

		//stuff that should have been removed
		$this->assertNull(CM_Model_StreamChannel_Abstract::findKey($streamChannelToBeRemoved->getKey()));
		$this->assertNull(CM_Model_Stream_Publish::findKey($streamPublishToBeRemoved->getKey()));
		$this->assertNull(CM_Model_Stream_Subscribe::findKey($streamSubscribeToBeRemoved1->getKey()));
		$this->assertNull(CM_Model_Stream_Subscribe::findKey($streamSubscribeToBeRemoved2->getKey()));
		$this->assertNull(CM_Model_Stream_Subscribe::findKey($streamSubscribeToBeRemoved3->getKey()));
	}

	public function testCheckStreams() {
		CM_Config::get()->CM_Model_StreamChannel_Abstract->types[CM_Model_StreamChannel_Video_Mock::TYPE] = 'CM_Model_StreamChannel_Video_Mock';
		$wowza = $wowza = $this->getMock('CM_Wowza', array('stop'));
		$wowza->expects($this->exactly(2))->method('stop')->will($this->returnValue(1));
		/** @var CM_Model_StreamChannel_Video_Mock $streamChannel */
		// allowedUntil will be updated, if stream has expired and it's user isn't $userUnchanged
		$userUnchanged = TH::createUser();
		$streamChannel = CM_Model_StreamChannel_Video_Mock::create(array('key' => 'foo1', 'serverId' => 1));
		$streamSubscribeUnchanged1 = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => $userUnchanged,
			'key' => 'foo1_2', 'start' => time(), 'allowedUntil' => time()));
		$streamSubscribeUnchanged2 = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => TH::createUser(),
			'key' => 'foo1_4', 'start' => time(), 'allowedUntil' => time() + 100));
		$streamSubscribeChanged1 = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => TH::createUser(),
			'key' => 'foo1_3', 'start' => time(), 'allowedUntil' => time()));
		$streamPublishUnchanged1 = CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $userUnchanged,
			'key' => 'foo1_2', 'start' => time(), 'allowedUntil' => time()));
		$streamPublishChanged1 = CM_Model_Stream_Publish::create(array('streamChannel' => CM_Model_StreamChannel_Video_Mock::create(array('key' => 'foo2',
			'serverId' => 1)), 'user' => TH::createUser(), 'key' => 'foo2_1', 'start' => time(), 'allowedUntil' => time()));

		TH::timeForward(5);
		$wowza->checkStreams();

		$this->assertEquals($streamSubscribeUnchanged1->getAllowedUntil(), $streamSubscribeUnchanged1->_change()->getAllowedUntil());
		$this->assertEquals($streamSubscribeUnchanged2->getAllowedUntil(), $streamSubscribeUnchanged2->_change()->getAllowedUntil());
		$this->assertEquals($streamSubscribeChanged1->getAllowedUntil() + 100, $streamSubscribeChanged1->_change()->getAllowedUntil());
		$this->assertEquals($streamPublishUnchanged1->getAllowedUntil(), $streamPublishUnchanged1->_change()->getAllowedUntil());
		$this->assertEquals($streamPublishChanged1->getAllowedUntil() + 100, $streamPublishChanged1->_change()->getAllowedUntil());
	}

	public function testGetServer() {
		$server = CM_Wowza::getServer(1);
		$this->assertSame('10.0.3.108', $server['privateIp']);

		try {
			CM_Wowza::getServer(800);
			$this->fail('Found server with id 800');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('No wowza server with id `800` found', $ex->getMessage());
		}
	}

	public function testGetSeverId() {
		$method = new ReflectionMethod('CM_Wowza', '_getServerId');
		$method->setAccessible(true);
		$this->assertEquals(1, $method->invoke(new CM_Wowza, '10.0.3.109'));
		$this->assertEquals(1, $method->invoke(new CM_Wowza, '10.0.3.108'));
		$this->assertEquals(1, $method->invoke(new CM_Wowza, 'wowza1.fuckbook.cat.cargomedia'));
		try {
			$method->invoke(new CM_Wowza, '66.66.66.66');
			$this->fail('Found server with ip 66.66.66.66');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('No wowza server with host `66.66.66.66` found', $ex->getMessage());
		}
	}

	private function _generateWowzaData(array $streamChannels) {
		$jsonData = array();
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		foreach ($streamChannels as $streamChannel) {
			$subscribes = array();
			/** @var CM_Model_Stream_Publish $streamPublish */
			$streamPublish = $streamChannel->getStreamPublish();
			/** @var CM_Model_Stream_Subscribe $streamSubscribe */
			foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
				$session = TH::createSession($streamSubscribe->getUser());
				$subscribes[$streamSubscribe->getKey()] = array('startTimeStamp' => $streamSubscribe->getStart(),
					'clientId' => $streamSubscribe->getKey(), 'data' => json_encode(array('sessionId' => $session->getId())));
				unset($session);
			}
			$session = TH::createSession($streamPublish->getUser());
			$jsonData[$streamChannel->getKey()] = array('startTimeStamp' => $streamPublish->getStart(), 'clientId' => $streamPublish->getKey(),
				'data' => json_encode(array('sessionId' => $session->getId(), 'streamChannelType' => $streamChannel->getType())),
				'subscribers' => $subscribes, 'thumbnailCount' => 2, 'width' => 480, 'height' => 720, 'wowzaIp' => ip2long('192.168.0.1'));
			unset($session);
		}
		return json_encode($jsonData);
	}
}

class CM_Model_StreamChannel_Video_Mock extends CM_Model_StreamChannel_Video {

	public function canPublish(CM_Model_User $user, $allowedUntil) {
		return $user->getId() != 1 ? $allowedUntil + 100 : $allowedUntil;
	}

	public function canSubscribe(CM_Model_User $user, $allowedUntil) {
		return $user->getId() != 1 ? $allowedUntil + 100 : $allowedUntil;
	}
}
