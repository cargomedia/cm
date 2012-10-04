<?php

class CM_Paging_User_StreamChannel extends CM_Paging_User_Abstract {

	public function __construct(CM_Model_StreamChannel_Abstract $streamChannel) {
		$pagings = array($streamChannel->getPublishers(), $streamChannel->getSubscribers());
		$source = new CM_PagingSource_Pagings($pagings, true);
		parent::__construct($source);
	}
}
