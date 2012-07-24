<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_StreamChannel_AbstractTest extends TestCase {

	static $_configBackup;

	public static function setUpBeforeClass() {
		self::$_configBackup = CM_Config::get();
		CM_Config::get()->CM_Model_StreamChannel_Abstract->types[1] = 'CM_Model_StreamChannel_Mock';
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
		CM_Config::set(self::$_configBackup);
	}

	public function setup() {
		if (!class_exists('CM_Model_StreamChannel_Mock')) {
			$this->getMockForAbstractClass('CM_Model_StreamChannel_Abstract', array(), 'CM_Model_StreamChannel_Mock', false);
		}
	}

	public function testConstructor() {
		try {
			new CM_Model_StreamChannel_Mock(123123);
			$this->fail('Can instantiate streamChannel without data.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testGetKey() {
		$id = CM_Mysql::insert(TBL_CM_STREAMCHANNEL, array('key' => 'foo', 'type' => 1));
		$streamChannel = new CM_Model_StreamChannel_Mock($id);
		$this->assertEquals('foo', $streamChannel->getKey());
	}

	public function testFactory() {
		$streamChannel = CM_Model_StreamChannel_Video::create(array('key' => 'dsljkfk34asdd'));
		$streamChannel = CM_Model_StreamChannel_Abstract::factory($streamChannel->getId());
		$this->assertInstanceOf('CM_Model_StreamChannel_Video', $streamChannel);

		$streamChannel = CM_Model_StreamChannel_Message::create(array('key' => 'asdasdaasadgss'));
		$streamChannel = CM_Model_StreamChannel_Abstract::factory($streamChannel->getId());
		$this->assertInstanceOf('CM_Model_StreamChannel_Message', $streamChannel);
	}

	public function testFindKey() {
		/** @var CM_Model_StreamChannel_Video $streamChannelOriginal */
		$streamChannelOriginal = TH::createStreamChannel(CM_Model_StreamChannel_Video::TYPE);
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey($streamChannelOriginal->getKey());
		$this->assertInstanceOf('CM_Model_StreamChannel_Video', $streamChannel);
		$this->assertEquals($streamChannelOriginal->getId(), $streamChannel->getId());
	}

	public function testDelete() {
		$id = CM_Mysql::insert(TBL_CM_STREAMCHANNEL, array('key' => 'bar', 'type' => 1));
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = new CM_Model_StreamChannel_Mock($id);
		CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => TH::createUser(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_1',));
		CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => TH::createUser(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '123123_2',));
		CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => TH::createUser(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '133123_3'));
		CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => TH::createUser(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '133123_4'));
		$this->assertEquals(2, $streamChannel->getStreamPublishs()->getCount());
		$this->assertEquals(2, $streamChannel->getStreamSubscribes()->getCount());
		$streamChannel->delete();
		try {
			new CM_Model_StreamChannel_Mock($id);
			$this->fail('streamChannel not deleted.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
		$this->assertEquals(0, $streamChannel->getStreamPublishs()->getCount());
		$this->assertEquals(0, $streamChannel->getStreamSubscribes()->getCount());
		$this->assertEquals(0, CM_Mysql::count(TBL_CM_STREAM_SUBSCRIBE, array('channelId' => $streamChannel->getId())), 'StreamSubscriptions not deleted');
		$this->assertEquals(0, CM_Mysql::count(TBL_CM_STREAM_PUBLISH, array('channelId' => $streamChannel->getId())), 'StreamPublishs not deleted');
	}

	public function testGetSubscribers() {
		$id = CM_Mysql::insert(TBL_CM_STREAMCHANNEL, array('key' => 'bar', 'type' => 1));
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = new CM_Model_StreamChannel_Mock($id);
		$this->assertEquals(0, $streamChannel->getSubscribers()->getCount());
		$streamSubscribe = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => TH::createUser(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '111_1'));
		$this->assertEquals(1, $streamChannel->getSubscribers()->getCount());
		$user = TH::createUser();
		CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123, 'allowedUntil' => 324234, 'key' => '111_2'));
		$this->assertEquals(2, $streamChannel->getSubscribers()->getCount());
		CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123, 'allowedUntil' => 324234, 'key' => '111_3'));
		$this->assertEquals(2, $streamChannel->getSubscribers()->getCount());
		$streamSubscribe->delete();
		$this->assertEquals(1, $streamChannel->getSubscribers()->getCount());
	}

	public function testGetPublishers() {
		$id = CM_Mysql::insert(TBL_CM_STREAMCHANNEL, array('key' => 'bar1', 'type' => 1));
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = new CM_Model_StreamChannel_Mock($id);
		$this->assertEquals(0, $streamChannel->getPublishers()->getCount());
		$streamPublish = CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => TH::createUser(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '111_1'));
		$this->assertEquals(1, $streamChannel->getPublishers()->getCount());
		$user = TH::createUser();
		CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123, 'allowedUntil' => 324234, 'key' => '111_2'));
		$this->assertEquals(2, $streamChannel->getPublishers()->getCount());
		CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123, 'allowedUntil' => 324234, 'key' => '111_3'));
		$this->assertEquals(2, $streamChannel->getPublishers()->getCount());
		$streamPublish->delete();
		$this->assertEquals(1, $streamChannel->getPublishers()->getCount());
	}

	public function testGetUsers() {
		$id = CM_Mysql::insert(TBL_CM_STREAMCHANNEL, array('key' => 'bar2', 'type' => 1));
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = new CM_Model_StreamChannel_Mock($id);
		$this->assertEquals(0, $streamChannel->getUsers()->getCount());
		$user = TH::createUser();
		CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '112_1'));
		$this->assertEquals(1, $streamChannel->getUsers()->getCount());
		CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '112_2'));
		$this->assertEquals(1, $streamChannel->getUsers()->getCount());
		CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123, 'allowedUntil' => 324234, 'key' => '112_3'));
		$this->assertEquals(1, $streamChannel->getUsers()->getCount());
		CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => TH::createUser(), 'start' => 123123, 'allowedUntil' => 324234, 'key' => '112_4'));
		$this->assertEquals(2, $streamChannel->getUsers()->getCount());
	}

	public function testCreate() {
		$streamChannel = CM_Model_StreamChannel_Abstract::createType(CM_Model_StreamChannel_Message::TYPE, array('key' => 'foo1'));
		$this->assertInstanceOf('CM_Model_StreamChannel_Message', $streamChannel);
	}
}
