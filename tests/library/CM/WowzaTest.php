<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_WowzaTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testSynchronize() {
		$streamChannels = array();
		$streamChannel = TH::createStreamChannel();
		$streamChannels[] = $streamChannel;
		TH::createStreamPublish(null, $streamChannel);
		TH::createStreamSubscribe(null, $streamChannel);
		TH::createStreamSubscribe(null, $streamChannel);
		TH::createStreamSubscribe(null, $streamChannel);
		$streamChannel = TH::createStreamChannel();
		$streamChannels[] = $streamChannel;
		TH::createStreamPublish(null, $streamChannel);
		TH::createStreamSubscribe(null, $streamChannel);
		TH::createStreamSubscribe(null, $streamChannel);
		$streamChannel = TH::createStreamChannel();
		$streamChannels[] = $streamChannel;
		$addedChannelKey = $streamChannel->getKey();
		$streamPublishToBeAdded = TH::createStreamPublish(null, $streamChannel);
		$streamSubscribeToBeAdded1 = TH::createStreamSubscribe(null, $streamChannel);
		$streamSubscribeToBeAdded2 = TH::createStreamSubscribe(null, $streamChannel);
		$wowzaData = $this->_generateWowzaData($streamChannels);
		$streamChannelToBeAdded = clone($streamChannel);
		$streamChannel->delete();
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
		CM_Wowza::synchronize($wowzaData);

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
			$jsonData[$streamPublish->getKey()] = array('startTimeStamp' => $streamPublish->getStart(), 'clientId' => $streamPublish->getKey(),
				'data' => json_encode(array('sessionId' => $session->getId(), 'streamType' => $streamChannel->getType())),
				'streamName' => $streamChannel->getKey(), 'subscribers' => $subscribes);
			unset($session);
		}
		return $jsonData;
	}
}
