<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_StreamChannel_VideoTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testCreate() {
		/** @var CM_Model_StreamChannel_Abstract $streamChannel */
		$streamChannel = CM_Model_StreamChannel_Video::create(array('key' => 'dsljkfk342gkfsd'));
		$this->assertInstanceOf('CM_Model_StreamChannel_Video', $streamChannel);
		$this->assertGreaterThanOrEqual(1, $streamChannel->getId());
		$this->assertRow(TBL_CM_STREAMCHANNEL, array('id' => $streamChannel->getId(), 'type' => CM_Model_StreamChannel_Video::TYPE, 'key' => 'dsljkfk342gkfsd'));
	}
}
