<?php

class CM_WowzaTest extends CMTest_TestCase {

	public function setUp() {
		CM_Config::get()->CM_Wowza->servers = array(1 => array('publicHost' => 'wowza1.fuckbook.cat.cargomedia', 'publicIp' => '10.0.3.109',
			'privateIp' => '10.0.3.108'));
	}

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testSynchronizeMissingInWowza() {
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
		$streamSubscribe = CMTest_TH::createStreamSubscribe(null, $streamChannel);

		/** @var CM_Wowza $wowza */
		$wowza = $this->getMock('CM_Wowza', array('fetchStatus'));
		$json = $this->_generateWowzaData(array());
		$wowza->expects($this->any())->method('fetchStatus')->will($this->returnValue($json));

		$wowza->synchronize();
		$this->assertEquals($streamChannel, CM_Model_StreamChannel_Abstract::findKey($streamChannel->getKey()));
		$this->assertEquals($streamPublish, CM_Model_Stream_Publish::findKey($streamPublish->getKey()));
		$this->assertEquals($streamSubscribe, CM_Model_Stream_Subscribe::findKey($streamSubscribe->getKey()));

		CMTest_TH::timeForward(5);
		$wowza->synchronize();
		$this->assertNull(CM_Model_StreamChannel_Abstract::findKey($streamChannel->getKey()));
		$this->assertNull(CM_Model_Stream_Publish::findKey($streamPublish->getKey()));
		$this->assertNull(CM_Model_Stream_Subscribe::findKey($streamSubscribe->getKey()));
	}

	public function testSynchronizeMissingInPhp() {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
		$streamSubscribe = CMTest_TH::createStreamSubscribe(null, $streamChannel);

		/** @var CM_Wowza $wowza */
		$wowza = $this->getMock('CM_Wowza', array('fetchStatus', '_stopClient'));
		$json = $this->_generateWowzaData(array($streamChannel));
		$wowza->expects($this->any())->method('fetchStatus')->will($this->returnValue($json));
		$wowza->expects($this->at(1))->method('_stopClient')->with($streamPublish->getKey(), $streamChannel->getPrivateHost());
		$wowza->expects($this->at(2))->method('_stopClient')->with($streamSubscribe->getKey(), $streamChannel->getPrivateHost());
		$wowza->expects($this->exactly(2))->method('_stopClient');
		$streamChannel->delete();

		$wowza->synchronize();
	}

	public function testCheckStreams() {
		CM_Config::get()->CM_Model_StreamChannel_Abstract->types[CM_Model_StreamChannel_Video_Mock::TYPE] = 'CM_Model_StreamChannel_Video_Mock';
		$wowza = $wowza = $this->getMock('CM_Wowza', array('stop'));
		$wowza->expects($this->exactly(2))->method('stop')->will($this->returnValue(1));
		/** @var CM_Model_StreamChannel_Video_Mock $streamChannel */
		// allowedUntil will be updated, if stream has expired and its user isn't $userUnchanged, hardcoded in CM_Model_StreamChannel_Video_Mock::canSubscribe() using getOnline()
		$userUnchanged = CMTest_TH::createUser();
		$userUnchanged->setOnline();
		$streamChannel = CM_Model_StreamChannel_Video_Mock::create(array('key' => 'foo1', 'serverId' => 1));
		$streamSubscribeUnchanged1 = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => $userUnchanged,
			'key' => 'foo1_2', 'start' => time(), 'allowedUntil' => time()));
		$streamSubscribeUnchanged2 = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => CMTest_TH::createUser(),
			'key' => 'foo1_4', 'start' => time(), 'allowedUntil' => time() + 100));
		$streamSubscribeChanged1 = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => CMTest_TH::createUser(),
			'key' => 'foo1_3', 'start' => time(), 'allowedUntil' => time()));
		$streamPublishUnchanged1 = CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $userUnchanged,
			'key' => 'foo1_2', 'start' => time(), 'allowedUntil' => time()));
		$streamPublishChanged1 = CM_Model_Stream_Publish::create(array('streamChannel' => CM_Model_StreamChannel_Video_Mock::create(array('key' => 'foo2',
			'serverId' => 1)), 'user' => CMTest_TH::createUser(), 'key' => 'foo2_1', 'start' => time(), 'allowedUntil' => time()));

		CMTest_TH::timeForward(5);
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
				$session = CMTest_TH::createSession($streamSubscribe->getUser());
				$subscribes[$streamSubscribe->getKey()] = array('startTimeStamp' => $streamSubscribe->getStart(),
					'clientId' => $streamSubscribe->getKey(), 'data' => json_encode(array('sessionId' => $session->getId())));
				unset($session);
			}
			$session = CMTest_TH::createSession($streamPublish->getUser());
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
		return $user->getOnline() ? $allowedUntil : $allowedUntil + 100;
	}

	public function canSubscribe(CM_Model_User $user, $allowedUntil) {
		return $user->getOnline() ? $allowedUntil : $allowedUntil + 100;
	}
}
