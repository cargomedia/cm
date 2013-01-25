<?php

class CM_Paging_FileUserContent_StreamChannelArchiveVideoThumbnailsTest extends CMTest_TestCase {

	public function testPaging() {
		$archive = CMTest_TH::createStreamChannelVideoArchive();
		$paging = new CM_Paging_FileUserContent_StreamChannelArchiveVideoThumbnails($archive);
		$this->assertSame(0, $paging->getCount());
		$this->assertSame(array(), $paging->getItems());

		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = CMTest_TH::createStreamChannel();
		$streamChannel->setThumbnailCount(4);
		$archive = CMTest_TH::createStreamChannelVideoArchive($streamChannel);
		$paging = new CM_Paging_FileUserContent_StreamChannelArchiveVideoThumbnails($archive);
		$this->assertSame(4, $paging->getCount());
		$filename = $archive->getId() . '-' . $archive->getHash() . '-thumbs/1.png';
		$this->assertEquals(new CM_File_UserContent('streamChannels', $filename, $streamChannel->getId()), $paging->getItem(0));
	}
}
