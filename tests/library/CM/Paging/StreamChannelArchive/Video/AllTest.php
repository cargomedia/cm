<?php
require_once __DIR__ . '/../../../../../TestCase.php';

class CM_Paging_StreamChannelArchiveVideo_AllTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testPaging() {
		$archive = TH::createStreamChannelVideoArchive();
		TH::createStreamChannelVideoArchive();
		TH::createStreamChannelVideoArchive();
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = TH::createStreamChannel();
		$streamChannel = $this->getMock('CM_Model_StreamChannel_Video', array('getType'), array($streamChannel->getId()));
		$streamChannel->expects($this->any())->method('getType')->will($this->returnValue(3));
		TH::createStreamChannelVideoArchive($streamChannel);

		$paging = new CM_Paging_StreamChannelArchiveVideo_All();
		$this->assertSame(4, $paging->getCount());

		$archive->delete();

		$paging = new CM_Paging_StreamChannelArchiveVideo_All();
		$this->assertSame(3, $paging->getCount());
	}
}