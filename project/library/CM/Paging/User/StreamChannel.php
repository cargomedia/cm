<?php

class CM_Paging_User_StreamChannel extends CM_Paging_User_Abstract {

	public function __construct(CM_StreamChannel $streamChannel) {
		$source = new CM_PagingSource_Sql('`userId`', TBL_CM_VIDEOSTREAM_SUBSCRIBE, '`publishId` = ' . $streamChannel->getStreamPublish()->getId());
		$source->enableCache();
		parent::__construct($source);
	}
}
