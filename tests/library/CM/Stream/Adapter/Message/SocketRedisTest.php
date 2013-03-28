<?php

class CM_Stream_Adapter_Message_SocketRedisTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testGetOptions() {
		CM_Config::get()->CM_Stream_Adapter_Message_SocketRedis->hostPrefix = true;
		CM_Config::get()->CM_Stream_Adapter_Message_SocketRedis->servers = array(
			array('httpHost' => 'foo', 'httpPort' => 8085, 'sockjsUrls' => array('http://stream:8090'))
		);
		$adapter = new CM_Stream_Adapter_Message_SocketRedis();
		$options = $adapter->getOptions();
		$this->assertArrayHasKey('sockjsUrl', $options);
		$this->assertRegExp('#http://[0-9]+.stream:8090#', $options['sockjsUrl']);
	}

	public function testOnRedisMessageSubscribe() {
		$adapter = new CM_Stream_Adapter_Message_SocketRedis();
		$message = array('type' => 'subscribe', 'data' => array('channel' => 'foo:' . CM_Model_StreamChannel_Message::TYPE, 'clientKey' => 'bar', 'data' => array()));
		$adapter->onRedisMessage(json_encode($message));
		$timeStarted = time();

		$streamChannel = CM_Model_StreamChannel_Message::findByKey('foo', $adapter->getType());
		$this->assertNotNull($streamChannel);
		$streamChannels = new CM_Paging_StreamChannel_AdapterType($adapter->getType());
		$this->assertSame(1, $streamChannels->getCount());
		$streamSubscribe = CM_Model_Stream_Subscribe::findByKeyAndChannel('bar', $streamChannel);
		$this->assertNotNull($streamSubscribe);
		$this->assertSame(1, $streamChannel->getStreamSubscribes()->getCount());
		$this->assertSameTime($timeStarted, $streamSubscribe->getStart());
		$this->assertSameTime(null, $streamSubscribe->getAllowedUntil());
		$this->assertNull($streamSubscribe->getUser());

		CMTest_TH::timeForward(CM_Stream_Adapter_Message_SocketRedis::SYNCHRONIZE_DELAY);
		$adapter->onRedisMessage(json_encode($message));
		$streamChannels = new CM_Paging_StreamChannel_AdapterType($adapter->getType());
		$this->assertSame(1, $streamChannels->getCount());
		$this->assertSame(1, $streamChannel->getStreamSubscribes()->getCount());
		CMTest_TH::reinstantiateModel($streamSubscribe);
		$this->assertSameTime($timeStarted, $streamSubscribe->getStart());
		$this->assertSameTime(null, $streamSubscribe->getAllowedUntil());
	}

	public function testOnRedisMessageSubscribeUser() {
		$adapter = new CM_Stream_Adapter_Message_SocketRedis();
		$user = CMTest_TH::createUser();
		$session = new CM_Session();
		$session->setUser($user);
		$session->write();
		$message = array('type' => 'subscribe',
						 'data' => array('channel' => 'foo:' . CM_Model_StreamChannel_Message::TYPE, 'clientKey' => 'bar', 'data' => array('sessionId' => $session->getId())));
		$adapter->onRedisMessage(json_encode($message));

		$streamChannel = CM_Model_StreamChannel_Message::findByKey('foo', $adapter->getType());
		$streamSubscribe = CM_Model_Stream_Subscribe::findByKeyAndChannel('bar', $streamChannel);
		$this->assertEquals($user, $streamSubscribe->getUser());
	}

	public function testOnRedisMessageSubscribeSessionInvalid() {
		$adapter = new CM_Stream_Adapter_Message_SocketRedis();
		$message = array('type' => 'subscribe',
						 'data' => array('channel' => 'foo:' . CM_Model_StreamChannel_Message::TYPE, 'clientKey' => 'bar', 'data' => array('sessionId' => 'foo')));
		$adapter->onRedisMessage(json_encode($message));

		$streamChannel = CM_Model_StreamChannel_Message::findByKey('foo', $adapter->getType());
		$streamSubscribe = CM_Model_Stream_Subscribe::findByKeyAndChannel('bar', $streamChannel);
		$this->assertNull($streamSubscribe->getUser());
	}

	public function testOnRedisMessageUnsubscribe() {
		$adapter = new CM_Stream_Adapter_Message_SocketRedis();
		$streamChannel = CM_Model_StreamChannel_Message::create(array('key' => 'foo', 'adapterType' => $adapter->getType()));
		CM_Model_Stream_Subscribe::create(array('key' => 'foo', 'streamChannel' => $streamChannel, 'start' => time(), 'allowedUntil' => null));
		CM_Model_Stream_Subscribe::create(array('key' => 'bar', 'streamChannel' => $streamChannel, 'start' => time(), 'allowedUntil' => null));

		$message = array('type' => 'unsubscribe', 'data' => array('channel' => 'foo', 'clientKey' => 'foo'));
		$adapter->onRedisMessage(json_encode($message));
		$streamChannel = CM_Model_StreamChannel_Message::findByKey('foo', $adapter->getType());
		$this->assertNotNull($streamChannel);
		$streamSubscribe = CM_Model_Stream_Subscribe::findByKeyAndChannel('foo', $streamChannel);
		$this->assertNull($streamSubscribe);

		$message = array('type' => 'unsubscribe', 'data' => array('channel' => 'foo', 'clientKey' => 'bar'));
		$adapter->onRedisMessage(json_encode($message));
		$streamChannel = CM_Model_StreamChannel_Message::findByKey('foo', $adapter->getType());
		$this->assertNull($streamChannel);
	}

	public function testSynchronize() {
		$jsTime =  (time() - CM_Stream_Adapter_Message_SocketRedis::SYNCHRONIZE_DELAY - 1) * 1000;
		for ($i = 0; $i < 2; $i++) {
			$status = array(
				'channel-foo:' . CM_Model_StreamChannel_Message::TYPE => array('subscribers' => array(
					'foo' => array('clientKey' => 'foo', 'subscribeStamp' => $jsTime, 'data' => array()),
					'bar' => array('clientKey' => 'bar', 'subscribeStamp' => $jsTime, 'data' => array()),
				)),
				'channel-bar:' . CM_Model_StreamChannel_Message::TYPE => array('subscribers' => array(
					'foo' => array('clientKey' => 'foo', 'subscribeStamp' => $jsTime, 'data' => array()),
					'bar' => array('clientKey' => 'bar', 'subscribeStamp' => $jsTime, 'data' => array()),
				)),
			);
			$this->_testSynchronize($status);
		}
		$status = array(
			'channel-foo:' . CM_Model_StreamChannel_Message::TYPE => array('subscribers' => array(
				'foo' => array('clientKey' => 'foo', 'subscribeStamp' => $jsTime, 'data' => array()),
			))
		);
		$this->_testSynchronize($status);
	}

	public function testSynchronizeIgnoreNewSubscribers() {
		$jsTime =  time() * 1000;
		$status = array(
			'channel-foo:' . CM_Model_StreamChannel_Message::TYPE => array('subscribers' => array(
				'foo' => array('clientKey' => 'foo', 'subscribeStamp' => $jsTime, 'data' => array()),
			))
		);
		$adapter = $this->getMockBuilder('CM_Stream_Adapter_Message_SocketRedis')->setMethods(array('_fetchStatus'))->getMock();
		$adapter->expects($this->any())->method('_fetchStatus')->will($this->returnValue($status));
		/** @var $adapter CM_Stream_Adapter_Message_SocketRedis */
		$adapter->synchronize();

		$this->assertNull(CM_Model_StreamChannel_Message::findByKey('channel-foo', $adapter->getType()));
		$subscribes = new CM_Paging_StreamSubscribe_AdapterType($adapter->getType());
		$this->assertSame(0, $subscribes->getCount());
	}

	public function testSynchronizeInvalidType() {
		$jsTime =  (time() - CM_Stream_Adapter_Message_SocketRedis::SYNCHRONIZE_DELAY - 1) * 1000;
		$status = array(
			'channel-foo:invalid-type' => array('subscribers' => array(
				'foo' => array('clientKey' => 'foo', 'subscribeStamp' => $jsTime, 'data' => array()),
			))
		);
		$adapter = $this->getMockBuilder('CM_Stream_Adapter_Message_SocketRedis')->setMethods(array('_fetchStatus', '_handleException'))->getMock();
		$adapter->expects($this->any())->method('_fetchStatus')->will($this->returnValue($status));
		$adapter->expects($this->once())->method('_handleException')->with(new PHPUnit_Framework_Constraint_ExceptionMessage('Type `0` not configured for class `CM_Model_StreamChannel_Message`.'));
		/** @var $adapter CM_Stream_Adapter_Message_SocketRedis */
		$adapter->synchronize();
	}

	/**
	 * @param array $status
	 */
	private function _testSynchronize($status) {
		$adapter = $this->getMockBuilder('CM_Stream_Adapter_Message_SocketRedis')->setMethods(array('_fetchStatus'))->getMock();
		$adapter->expects($this->any())->method('_fetchStatus')->will($this->returnValue($status));
		/** @var $adapter CM_Stream_Adapter_Message_SocketRedis */
		$adapter->synchronize();

		$streamChannels = new CM_Paging_StreamChannel_AdapterType($adapter->getType());
		$this->assertSame(count($status), $streamChannels->getCount());
		/** @var $streamChannel CM_Model_StreamChannel_Message */
		foreach ($streamChannels as $streamChannel) {
			$this->assertInstanceOf('CM_Model_StreamChannel_Message', $streamChannel);
			$channel = $streamChannel->getKey() . ':' . CM_Model_StreamChannel_Message::TYPE;
			$this->assertSame(count($status[$channel]['subscribers']), $streamChannel->getStreamSubscribes()->getCount());
		}
		foreach ($status as $channel => $channelData) {
			list ($channelKey, $channelType) = explode(':', $channel);
			$streamChannel = CM_Model_StreamChannel_Message::findByKey($channelKey, $adapter->getType());
			$this->assertInstanceOf('CM_Model_StreamChannel_Message', $streamChannel);
			foreach ($channelData['subscribers'] as $clientKey => $subscriberData) {
				$subscribe = CM_Model_Stream_Subscribe::findByKeyAndChannel($clientKey, $streamChannel);
				$this->assertInstanceOf('CM_Model_Stream_Subscribe', $subscribe);
				$this->assertSameTime(time() - CM_Stream_Adapter_Message_SocketRedis::SYNCHRONIZE_DELAY - 1, $subscribe->getStart());
				$this->assertNull($subscribe->getAllowedUntil());
			}
		}
	}
}
