<?php

class CM_Stream_Adapter_Message_SocketRedisTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testOnRedisMessageSubscribe() {
		$adapter = new CM_Stream_Adapter_Message_SocketRedis();
		$message = array('type' => 'subscribe', 'data' => array('channel' => 'foo', 'clientKey' => 'bar', 'data' => array()));
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
		$this->assertNull($streamSubscribe->getUser());

		CMTest_TH::timeForward(10);
		$adapter->onRedisMessage(json_encode($message));
		$streamChannels = new CM_Paging_StreamChannel_AdapterType($adapter->getType());
		$this->assertSame(1, $streamChannels->getCount());
		$this->assertSame(1, $streamChannel->getStreamSubscribes()->getCount());
		CMTest_TH::reinstantiateModel($streamSubscribe);
		$this->assertSameTime($timeStarted, $streamSubscribe->getStart());
	}

	public function testOnRedisMessageSubscribeUser() {
		$adapter = new CM_Stream_Adapter_Message_SocketRedis();
		$user = CMTest_TH::createUser();
		$session = new CM_Session();
		$session->setUser($user);
		$session->write();
		$message = array('type' => 'subscribe',
			'data' => array('channel' => 'foo', 'clientKey' => 'bar', 'data' => array('sessionId' => $session->getId())));
		$adapter->onRedisMessage(json_encode($message));

		$streamChannel = CM_Model_StreamChannel_Message::findByKey('foo', $adapter->getType());
		$streamSubscribe = CM_Model_Stream_Subscribe::findByKeyAndChannel('bar', $streamChannel);
		$this->assertEquals($user, $streamSubscribe->getUser());
	}

	public function testOnRedisMessageUnsubscribe() {
		$adapter = new CM_Stream_Adapter_Message_SocketRedis();
		$streamChannel = CM_Model_StreamChannel_Message::create(array('key' => 'foo', 'adapterType' => $adapter->getType()));
		CM_Model_Stream_Subscribe::create(array('key' => 'foo', 'streamChannel' => $streamChannel, 'start' => time()));
		CM_Model_Stream_Subscribe::create(array('key' => 'bar', 'streamChannel' => $streamChannel, 'start' => time()));

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
		$status = array(
			'channel-foo' => array('subscribers' => array(
				array('clientKey' => 'foo', 'subscribeStamp' => time(), 'data' => array()),
				array('clientKey' => 'bar', 'subscribeStamp' => time(), 'data' => array()),
			)),
			'channel-bar' => array('subscribers' => array(
				array('clientKey' => 'foo', 'subscribeStamp' => time(), 'data' => array()),
				array('clientKey' => 'bar', 'subscribeStamp' => time(), 'data' => array()),
			)),
		);
		$adapter = $this->getMockBuilder('CM_Stream_Adapter_Message_SocketRedis')->setMethods(array('_fetchStatus'))->getMock();
		$adapter->expects($this->any())->method('_fetchStatus')->will($this->returnValue(json_encode($status)));
	}
}
