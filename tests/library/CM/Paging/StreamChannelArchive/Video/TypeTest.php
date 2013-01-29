<?php

class CM_Paging_StreamChannelArchiveVideo_TypeTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testPaging() {
		CMTest_TH::createStreamChannelVideoArchive();
		$archive = CMTest_TH::createStreamChannelVideoArchive();
		CMTest_TH::timeForward(30);
		CMTest_TH::createStreamChannelVideoArchive();
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamChannel = $this->getMock('CM_Model_StreamChannel_Video', array('getType'), array($streamChannel->getId()));
		$streamChannel->expects($this->any())->method('getType')->will($this->returnValue(3));
		CMTest_TH::createStreamChannelVideoArchive($streamChannel);

		$paging = new CM_Paging_StreamChannelArchiveVideo_Type(CM_Model_StreamChannel_Video::TYPE);
		$this->assertSame(3, $paging->getCount());

		$paging = new CM_Paging_StreamChannelArchiveVideo_Type($streamChannel->getType());
		$this->assertSame(1, $paging->getCount());

		$paging = new CM_Paging_StreamChannelArchiveVideo_Type(CM_Model_StreamChannel_Video::TYPE, $archive->getCreated());
		$this->assertSame(2, $paging->getCount());
	}
}
