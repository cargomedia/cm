<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_StreamChannel_AbstractTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testConstructor() {
		try {
			$this->getMockForAbstractClass('CM_Model_StreamChannel_Abstract', array(1));
			$this->fail('Can instantiate streamChannel without data.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testGetKey() {
		CM_Mysql::insert(TBL_CM_STREAMCHANNEL, array('key' => 'foo', 'type' => 1));
		$streamChannel = $this->getMockForAbstractClass('CM_Model_StreamChannel_Abstract', array(1));
		$this->assertEquals('foo', $streamChannel->getKey());
	}

	public function testFactory() {
		$streamChannel = CM_Model_StreamChannel_Video::create(array('key' => 'dsljkfk34asdd'));
		$streamChannel = CM_Model_StreamChannel_Abstract::factory($streamChannel->getId());
		$this->assertInstanceOf('CM_Model_StreamChannel_Video', $streamChannel);
	}

	public function testFindKey() {
		$id = CM_Mysql::insert(TBL_CM_STREAMCHANNEL, array('key' => 'testKey', 'type' => CM_Model_StreamChannel_Video::TYPE));
		$streamChannel = CM_Model_StreamChannel_Abstract::findKey('testKey');
		$this->assertInstanceOf('CM_Model_StreamChannel_Video', $streamChannel);
		$this->assertEquals($id, $streamChannel->getId());
	}

	public function testDelete() {
		$id = CM_Mysql::insert(TBL_CM_STREAMCHANNEL, array('key' => 'bar', 'type' => 1));
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = $this->getMockForAbstractClass('CM_Model_StreamChannel_Abstract', array($id));
		$streamChannel->getStreamPublishs()->add(array('user' => TH::createUser(), 'start' => 123123, 'allowedUntil' => 324234,
					'key' => '123123_2', 'name' => '123123qadadsw123'));
		$streamChannel->getStreamSubscribes()->add(array('user' => TH::createUser(), 'start' => 123123, 'allowedUntil' => 324234,
			'key' => '133123_1'));
		$streamChannel->delete();
		try {
			$this->getMockForAbstractClass('CM_Model_StreamChannel_Abstract', array($streamChannel->getId()));
			$this->fail('streamChannel not deleted.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
		$this->assertEquals(0, CM_Mysql::count(TBL_CM_STREAM_SUBSCRIBE, array('channelId' => $streamChannel->getId())), 'StreamSubscriptions not deleted');
		$this->assertEquals(0, CM_Mysql::count(TBL_CM_STREAM_PUBLISH, array('channelId' => $streamChannel->getId())), 'StreamPublishs not deleted');
	}
}
