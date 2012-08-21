<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Paging_File_StreamChannelArchiveVideoThumbnailsTest extends TestCase {

	public function testPaging() {
		$archive = TH::createStreamChannelVideoArchive();
		$paging = new CM_Paging_File_StreamChannelArchiveVideoThumbnails($archive);
		$this->assertSame(0, $paging->getCount());
		$this->assertSame(array(), $paging->getItems());

		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = TH::createStreamChannel();
		$streamChannel->setThumbnailCount(4);
		$archive = TH::createStreamChannelVideoArchive($streamChannel);
		$paging = new CM_Paging_File_StreamChannelArchiveVideoThumbnails($archive);
		$this->assertSame(4, $paging->getCount());
		$this->assertEquals($archive->getThumbnail(1), $paging->getItem(0));
	}
}