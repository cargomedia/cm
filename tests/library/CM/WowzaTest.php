<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_WowzaTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testSynchronize() {
		/** @var CM_Wowza $wowza */
		$wowza = $this->getMock('CM_Wowza', array('fetchStatus', 'stop'));
		$streamChannels = array();
		$streamChannel = TH::createStreamChannel();
		$streamChannels[] = $streamChannel;
		TH::createStreamPublish(null, $streamChannel);
		TH::createStreamSubscribe(null, $streamChannel);
		TH::createStreamSubscribe(null, $streamChannel);
		TH::createStreamSubscribe(null, $streamChannel);
		$streamChannel1 = TH::createStreamChannel();
		$streamChannels[] = $streamChannel1;
		TH::createStreamPublish(null, $streamChannel1);
		TH::createStreamSubscribe(null, $streamChannel1);
		$streamChannel = TH::createStreamChannel();
		$streamChannels[] = $streamChannel;
		$streamPublishToBeAdded = TH::createStreamPublish(null, $streamChannel);
		$streamSubscribeToBeAdded1 = TH::createStreamSubscribe(null, $streamChannel);
		$streamSubscribeToBeAdded2 = TH::createStreamSubscribe(null, $streamChannel);
		$json = $this->_generateWowzaData($streamChannels);
		$wowza->expects($this->any())->method('fetchStatus')->will($this->returnValue($json));
		$streamChannelToBeAdded = clone($streamChannel);
		$streamChannel->delete();
		$streamSubscribeToBeRemoved3 = TH::createStreamSubscribe(null, $streamChannel1);
		try {
			new CM_Model_StreamChannel_Video($streamChannelToBeAdded->getId());
			$this->fail();
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
		$streamChannelToBeRemoved = TH::createStreamChannel();
		$streamPublishToBeRemoved = TH::createStreamPublish(null, $streamChannelToBeRemoved);
		$streamSubscribeToBeRemoved1 = TH::createStreamSubscribe(null, $streamChannelToBeRemoved);
		$streamSubscribeToBeRemoved2 = TH::createStreamSubscribe(null, $streamChannelToBeRemoved);

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

		//stuff that should have been removed
		$this->assertNull(CM_Model_StreamChannel_Abstract::findKey($streamChannelToBeRemoved->getKey()));
		$this->assertNull(CM_Model_Stream_Publish::findKey($streamPublishToBeRemoved->getKey()));
		$this->assertNull(CM_Model_Stream_Subscribe::findKey($streamSubscribeToBeRemoved1->getKey()));
		$this->assertNull(CM_Model_Stream_Subscribe::findKey($streamSubscribeToBeRemoved2->getKey()));
		$this->assertNull(CM_Model_Stream_Subscribe::findKey($streamSubscribeToBeRemoved3->getKey()));
	}

	public function testCheckStreams() {
		TH::clearEnv();
		$configBackup = CM_Config::get();
		$mockType = 100;
		CM_Config::get()->CM_Model_Abstract->types[$mockType] = 'CM_Model_StreamChannel_Mock';
		CM_Config::get()->CM_Model_StreamChannel_Abstract->types[$mockType] = 'CM_Model_StreamChannel_Mock';
		CM_Config::get()->CM_Wowza->streamChannelTypes[] = $mockType;
		$wowza = $wowza = $this->getMock('CM_Wowza', array('stop'));
		$wowza->expects($this->exactly(2))->method('stop')->will($this->returnValue(1));
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		// allowedUntil will be updated, if stream has expired and it's user isn't $userUnchanged
		$userUnchanged = TH::createUser();
		$streamChannel = CM_Model_StreamChannel_Mock::create(array('key' => 'foo1'));
		$streamSubscribeUnchanged1 = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => $userUnchanged, 'key' => 'foo1_2', 'start' => time(),
			'allowedUntil' => 0));
		$streamSubscribeUnchanged2 = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => TH::createUser(), 'key' => 'foo1_4',
			'start' => time(), 'allowedUntil' => time() + 100));
		$streamSubscribeChanged1 = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => TH::createUser(), 'key' => 'foo1_3', 'start' => time(),
			'allowedUntil' => 0));
		$streamPublishUnchanged1 = CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $userUnchanged,
			'key' => 'foo1_2', 'start' => time(), 'allowedUntil' => 0));
		$streamPublishChanged1 = CM_Model_Stream_Publish::create(array('streamChannel' => CM_Model_StreamChannel_Mock::create(array('key' => 'foo2')),
			'user' => TH::createUser(), 'key' => 'foo2_1', 'start' => time(), 'allowedUntil' => 0));
		$wowza->checkStreams();
		$this->assertEquals($streamSubscribeUnchanged1->getAllowedUntil(), $streamSubscribeUnchanged1->_change()->getAllowedUntil());
		$this->assertEquals($streamSubscribeUnchanged2->getAllowedUntil(), $streamSubscribeUnchanged2->_change()->getAllowedUntil());
		$this->assertEquals($streamSubscribeChanged1->getAllowedUntil() + 100, $streamSubscribeChanged1->_change()->getAllowedUntil());
		$this->assertEquals($streamPublishUnchanged1->getAllowedUntil(), $streamPublishUnchanged1->_change()->getAllowedUntil());
		$this->assertEquals($streamPublishChanged1->getAllowedUntil() + 100, $streamPublishChanged1->_change()->getAllowedUntil());

		CM_Config::set($configBackup);
	}

	private function _generateWowzaData(array $streamChannels) {
		$jsonData = array();
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		foreach ($streamChannels as $streamChannel) {
			$subscribes = array();
			/** @var CM_Model_Stream_Publish $streamPublish */
			$streamPublish = $streamChannel->getStreamPublishs()->getItem(0);
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
				'subscribers' => $subscribes);
			unset($session);
		}
		return json_encode($jsonData);
	}
}

class CM_Model_StreamChannel_Mock extends CM_Model_StreamChannel_Abstract {

	const TYPE = 100;

	public function canPublish(CM_Model_User $user, $allowedUntil) {
		return $user->getId() != 1 ? $allowedUntil + 100 : $allowedUntil;
	}

	public function canSubscribe(CM_Model_User $user, $allowedUntil) {
		return $user->getId() != 1 ? $allowedUntil + 100 : $allowedUntil;
	}

	/**
	 * @param CM_Model_Stream_Publish $streamPublish
	 */
	public function onPublish(CM_Model_Stream_Publish $streamPublish) {
		// TODO: Implement onPublish() method.
	}

	/**
	 * @param CM_Model_Stream_Subscribe $streamSubscribe
	 */
	public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
		// TODO: Implement onSubscribe() method.
	}

	/**
	 * @param CM_Model_Stream_Publish $streamPublish
	 */
	public function onUnpublish(CM_Model_Stream_Publish $streamPublish) {
		// TODO: Implement onUnpublish() method.
	}

	/**
	 * @param CM_Model_Stream_Subscribe $streamSubscribe
	 */
	public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
		// TODO: Implement onUnsubscribe() method.
	}

}
