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
		$wowza = $this->getMock('CM_Wowza', array('fetchData', 'stop'));
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
		$addedChannelKey = $streamChannel->getKey();
		$streamPublishToBeAdded = TH::createStreamPublish(null, $streamChannel);
		$streamSubscribeToBeAdded1 = TH::createStreamSubscribe(null, $streamChannel);
		$streamSubscribeToBeAdded2 = TH::createStreamSubscribe(null, $streamChannel);
		$json = $this->_generateWowzaData($streamChannels);
		$wowza->expects($this->any())->method('fetchData')->will($this->returnValue($json));
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

	private function _generateWowzaData(array $streamChannels) {
		$jsonData = array();
		$i = 0;
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		foreach ($streamChannels as $streamChannel) {
			$j = 0;
			$subscribes = array();
			/** @var CM_Model_Stream_Publish $streamPublish */
			$streamPublish = $streamChannel->getStreamPublishs()->getItem(0);
			/** @var CM_Model_Stream_Subscribe $streamSubscribe */
			foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
				$session = TH::createSession($streamSubscribe->getUser());
				$subscribes['sub' . ++$j] = array('startTimeStamp' => $streamSubscribe->getStart(), 'clientId' => $streamSubscribe->getKey(),
					'data' => json_encode(array('sessionId' => $session->getId())));
				unset($session);
			}
			$session = TH::createSession($streamPublish->getUser());
			$jsonData['sub' . ++$i] = array('startTimeStamp' => $streamPublish->getStart(), 'clientId' => $streamPublish->getKey(),
				'data' => json_encode(array('sessionId' => $session->getId(), 'streamType' => $streamChannel->getType())),
				'streamName' => $streamChannel->getKey(), 'subscribers' => $subscribes);
			unset($session);
		}
		return json_encode($jsonData);
	}
}
