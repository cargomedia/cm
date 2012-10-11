<?php
require_once __DIR__ . '/../../../../../TestCase.php';

class CM_Paging_StreamChannelArchiveVideo_TypeTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testPaging() {
		TH::createStreamChannelVideoArchive();
		TH::createStreamChannelVideoArchive();
		TH::timeForward(30);
		TH::createStreamChannelVideoArchive();
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = TH::createStreamChannel();
		$streamChannel = $this->getMock('CM_Model_StreamChannel_Video', array('getType'), array($streamChannel->getId()));
		$streamChannel->expects($this->any())->method('getType')->will($this->returnValue(3));
		TH::createStreamChannelVideoArchive($streamChannel);

		$paging = new CM_Paging_StreamChannelArchiveVideo_Type(CM_Model_StreamChannel_Video::TYPE);
		$this->assertSame(3, $paging->getCount());

		$paging = new CM_Paging_StreamChannelArchiveVideo_Type($streamChannel->getType());
		$this->assertSame(1, $paging->getCount());

		$paging = new CM_Paging_StreamChannelArchiveVideo_Type(CM_Model_StreamChannel_Video::TYPE, time() - 30);
		$this->assertSame(2, $paging->getCount());
	}
}