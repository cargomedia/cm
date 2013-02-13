<?php

class CM_VideoTest extends CMTest_TestCase {

	public function setUp() {
		CM_Config::get()->CM_Stream_Video->servers = array(1 => array('publicHost' => 'video.example.com', 'publicIp' => '10.0.3.109',
			'privateIp' => '10.0.3.108'));
	}

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testCheckStreams() {
		$mockAdapter = $this->getMockForAbstractClass('CM_Stream_Adapter_Video_Abstract', array(), 'CM_Stream_Adapter_Video_Mock', true, true, true, array('_stopClient'));
		$mockAdapter->expects($this->exactly(2))->method('_stopClient')->will($this->returnValue(1));

		CM_Config::get()->CM_Model_StreamChannel_Abstract->types[CM_Model_StreamChannel_Video_Mock::TYPE] = 'CM_Model_StreamChannel_Video_Mock';
		$wowza = $wowza = $this->getMock('CM_Stream_Video', array('_getAdapter'));
		$wowza->expects($this->any())->method('_getAdapter')->will($this->returnValue($mockAdapter));
		/** @var $wowza CM_Stream_Video */

		// allowedUntil will be updated, if stream has expired and its user isn't $userUnchanged, hardcoded in CM_Model_StreamChannel_Video_Mock::canSubscribe() using getOnline()
		$userUnchanged = CMTest_TH::createUser();
		$userUnchanged->setOnline();
		$streamChannel = CM_Model_StreamChannel_Video_Mock::create(array('key' => 'foo1', 'serverId' => 1, 'adapterType' => 1));
		$streamSubscribeUnchanged1 = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => $userUnchanged,
			'key' => 'foo1_2', 'start' => time(), 'allowedUntil' => time()));
		$streamSubscribeUnchanged2 = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => CMTest_TH::createUser(),
			'key' => 'foo1_4', 'start' => time(), 'allowedUntil' => time() + 100));
		$streamSubscribeChanged1 = CM_Model_Stream_Subscribe::create(array('streamChannel' => $streamChannel, 'user' => CMTest_TH::createUser(),
			'key' => 'foo1_3', 'start' => time(), 'allowedUntil' => time()));
		$streamPublishUnchanged1 = CM_Model_Stream_Publish::create(array('streamChannel' => $streamChannel, 'user' => $userUnchanged,
			'key' => 'foo1_2', 'start' => time(), 'allowedUntil' => time()));
		$streamPublishChanged1 = CM_Model_Stream_Publish::create(array('streamChannel' => CM_Model_StreamChannel_Video_Mock::create(array('key' => 'foo2',
			'serverId' => 1, 'adapterType' => 1)), 'user' => CMTest_TH::createUser(), 'key' => 'foo2_1', 'start' => time(), 'allowedUntil' => time()));

		CMTest_TH::timeForward(5);
		$wowza->checkStreams();

		$this->assertEquals($streamSubscribeUnchanged1->getAllowedUntil(), $streamSubscribeUnchanged1->_change()->getAllowedUntil());
		$this->assertEquals($streamSubscribeUnchanged2->getAllowedUntil(), $streamSubscribeUnchanged2->_change()->getAllowedUntil());
		$this->assertEquals($streamSubscribeChanged1->getAllowedUntil() + 100, $streamSubscribeChanged1->_change()->getAllowedUntil());
		$this->assertEquals($streamPublishUnchanged1->getAllowedUntil(), $streamPublishUnchanged1->_change()->getAllowedUntil());
		$this->assertEquals($streamPublishChanged1->getAllowedUntil() + 100, $streamPublishChanged1->_change()->getAllowedUntil());
	}

	public function testGetServer() {
		$server = CM_Stream_Video::getInstance()->getServer(1);
		$this->assertSame('10.0.3.108', $server['privateIp']);

		try {
			CM_Stream_Video::getInstance()->getServer(800);
			$this->fail('Found server with id 800');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('No video server with id `800` found', $ex->getMessage());
		}
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
