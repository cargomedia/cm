<?php

class CM_Paging_User_VideoStreamPublish extends CM_Paging_User_Abstract {

	public function __construct(CM_VideoStream_Publish $videoStreamPublish) {
		$source = new CM_PagingSource_Sql('`userId`', TBL_CM_VIDEOSTREAM_SUBSCRIBE, '`publishId` = ' . $videoStreamPublish->getId());
		$source->enableCache();
		parent::__construct($source);
	}
}
