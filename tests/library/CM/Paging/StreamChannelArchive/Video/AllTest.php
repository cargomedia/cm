<?php

class CM_Paging_StreamChannelArchiveVideo_AllTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testPaging() {
		$archive = CMTest_TH::createStreamChannelVideoArchive();
		CMTest_TH::createStreamChannelVideoArchive();
		CMTest_TH::createStreamChannelVideoArchive();
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamChannel = $this->getMock('CM_Model_StreamChannel_Video', array('getType'), array($streamChannel->getId()));
		$streamChannel->expects($this->any())->method('getType')->will($this->returnValue(3));
		CMTest_TH::createStreamChannelVideoArchive($streamChannel);

		$paging = new CM_Paging_StreamChannelArchiveVideo_All();
		$this->assertSame(4, $paging->getCount());

		$archive->delete();

		$paging = new CM_Paging_StreamChannelArchiveVideo_All();
		$this->assertSame(3, $paging->getCount());
	}
}
