<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Paging_FileUserContent_StreamChannelVideoThumbnailsTest extends TestCase {

	public function testPaging() {
		$streamChannel = TH::createStreamChannel();
		$paging = new CM_Paging_FileUserContent_StreamChannelVideoThumbnails($streamChannel);
		$this->assertSame(0, $paging->getCount());
		$this->assertSame(array(), $paging->getItems());

		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = TH::createStreamChannel();
		TH::createStreamPublish(null, $streamChannel);
		$streamChannel->setThumbnailCount(4);
		$paging = new CM_Paging_FileUserContent_StreamChannelVideoThumbnails($streamChannel);
		$this->assertSame(4, $paging->getCount());
		$filename = $streamChannel->getId() . '-' . $streamChannel->getHash() . '-thumbs/1.jpg';
		$this->assertEquals(new CM_File_UserContent('streamChannels', $filename, $streamChannel->getId()), $paging->getItem(0));
	}
}