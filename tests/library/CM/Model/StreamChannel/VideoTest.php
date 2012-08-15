<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_StreamChannel_VideoTest extends TestCase {

	public function setup() {
		TH::clearEnv();
	}

	public function tearDown() {
		TH::clearEnv();
	}

	public function testCreate() {
		/** @var CM_Model_StreamChannel_Video $channel */
		$channel = CM_Model_StreamChannel_Video::create(array('key' => 'foo', 'width' => 100, 'height' => 200, 'wowzaIp' => ip2long('127.0.0.1')));
		$this->assertInstanceOf('CM_Model_StreamChannel_Video', $channel);
		$this->assertSame(100, $channel->getWidth());
		$this->assertSame(200, $channel->getHeight());
		$this->assertSame('127.0.0.1', long2ip($channel->getWowzaIp()));
		$this->assertSame('foo', $channel->getKey());
	}

	public function testOnDelete() {
		$streamChannel = TH::createStreamChannel();
		$streamChannel->setArchive(null);
		$streamChannel->delete();
		try {
			new CM_Model_StreamChannel_Video($streamChannel->getId());
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
		$this->assertNotRow(TBL_CM_STREAMCHANNEL_VIDEO, array('id' => $streamChannel->getId()));
	}
}

