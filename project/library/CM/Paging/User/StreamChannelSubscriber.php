<?php

class CM_Paging_User_StreamChannelSubscriber extends CM_Paging_User_Abstract {

	public function __construct(CM_Model_StreamChannel_Abstract $streamChannel) {
		$source = new CM_PagingSource_Sql('DISTINCT `userId`', TBL_CM_STREAM_SUBSCRIBE,
				'`channelId` = ' . $streamChannel->getId() . ' AND `userId` IS NOT NULL');
		$source->enableCache();
		parent::__construct($source);
	}
}
