
<?php

class CM_Paging_User_StreamChannelPublisher extends CM_Paging_User_Abstract {

	public function __construct(CM_Model_StreamChannel_Abstract $streamChannel) {
		$source = new CM_PagingSource_Sql('DISTINCT `userId`', TBL_CM_STREAM_PUBLISH, '`channelId` = ' . $streamChannel->getId());
		$source->enableCache();
		parent::__construct($source);
	}
}
