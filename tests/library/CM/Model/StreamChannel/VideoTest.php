<?php

class CM_Model_StreamChannel_VideoTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
		CM_Config::get()->CM_Wowza->servers = array(1 => array('publicHost' => 'wowza1.fuckbook.cat.cargomedia', 'privateIp' => '10.0.3.108'));
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testCreate() {
		/** @var CM_Model_StreamChannel_Video $channel */
		$channel = CM_Model_StreamChannel_Video::create(array('key' => 'foo', 'width' => 100, 'height' => 200, 'serverId' => 1,
			'thumbnailCount' => 2));
		$this->assertInstanceOf('CM_Model_StreamChannel_Video', $channel);
		$this->assertSame(100, $channel->getWidth());
		$this->assertSame(200, $channel->getHeight());
		$this->assertSame('10.0.3.108', $channel->getPrivateHost());
		$this->assertSame('wowza1.fuckbook.cat.cargomedia', $channel->getPublicHost());
		$this->assertSame('foo', $channel->getKey());
		$this->assertSame(2, $channel->getThumbnailCount());
	}

	public function testCreateWithoutServerId() {
		try {
			CM_Model_StreamChannel_Video::create(array('key' => 'bar', 'width' => 100, 'height' => 200, 'serverId' => null, 'thumbnailCount' => 2));
			$this->fail('Can create streamChannel without wowzaIp');
		} catch (CM_Exception $ex) {
			$this->assertContains("`Column 'serverId' cannot be null`", $ex->getMessage());
		}
	}

	public function testNonexistentServerId() {
		/** @var CM_Model_StreamChannel_Video $channel */
		$channel = CM_Model_StreamChannel_Video::create(array('key' => 'foobar', 'width' => 100, 'height' => 200, 'serverId' => 800,
			'thumbnailCount' => 2));

		try {
			$channel->getPublicHost();
			$this->fail('Found server with Id 800');
		} catch (CM_Exception $ex) {
			$this->assertSame("No wowza server with id `800` found", $ex->getMessage());
		}
	}

	public function testGetStreamPublish() {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = CMTest_TH::createStreamChannel();
		try {
			$streamChannel->getStreamPublish();
			$this->fail();
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('has no StreamPublish.', $ex->getMessage());
		}
		$streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
		$this->assertModelEquals($streamPublish, $streamChannel->getStreamPublish());
	}

	public function testHasStreamPublish() {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = CMTest_TH::createStreamChannel();
		$this->assertFalse($streamChannel->hasStreamPublish());
		CMTest_TH::createStreamPublish(null, $streamChannel);
		$this->assertTrue($streamChannel->hasStreamPublish());

	}

	public function testThumbnailCount() {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamChannel->setThumbnailCount(15);
		$this->assertSame(15, $streamChannel->getThumbnailCount());
	}

	public function testOnDelete() {
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamChannel->delete();
		try {
			new CM_Model_StreamChannel_Video($streamChannel->getId());
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
		$this->assertNotRow(TBL_CM_STREAMCHANNEL_VIDEO, array('id' => $streamChannel->getId()));
	}

	public function testOnBeforeDelete() {
		$streamChannel = CMTest_TH::createStreamChannel();
		CMTest_TH::createStreamPublish(null, $streamChannel);
		try {
			new CM_Model_StreamChannelArchive_Video($streamChannel->getId());
			$this->fail('Archive exists before StreamChannel deleted.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
		$streamChannel->delete();
		try {
			new CM_Model_StreamChannelArchive_Video($streamChannel->getId());
			$this->assertTrue(true);
		} catch (CM_Exception_Nonexistent $ex) {
			$this->fail('Archive was not created.');
		}

		//without streamPublish
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamChannel->delete();
		try {
			new CM_Model_StreamChannelArchive_Video($streamChannel->getId());
			$this->fail('Archive created despite missing streamPublish.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testGetThumbnails() {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = CMTest_TH::createStreamChannel();
		CMTest_TH::createStreamPublish(null, $streamChannel);
		$this->assertSame(array(), $streamChannel->getThumbnails()->getItems());
		$streamChannel->setThumbnailCount(2);
		$thumb1 = new CM_File_UserContent('streamChannels',
				$streamChannel->getId() . '-' . $streamChannel->getHash() . '-thumbs/1.png', $streamChannel->getId());
		$thumb2 = new CM_File_UserContent('streamChannels',
				$streamChannel->getId() . '-' . $streamChannel->getHash() . '-thumbs/2.png', $streamChannel->getId());
		$this->assertEquals(array($thumb1, $thumb2), $streamChannel->getThumbnails()->getItems());
	}
}

