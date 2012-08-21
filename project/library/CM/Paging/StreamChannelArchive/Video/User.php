<?php

class CM_Paging_StreamChannelArchive_Video_User extends CM_Paging_StreamChannelArchive_Video_Abstract {

	public function __construct(CM_Model_User $user) {
		$source = new CM_PagingSource_Sql('id', TBL_CM_STREAMCHANNELARCHIVE_VIDEO, '`userId` = ' . $user->getId(), 'createStamp DESC');
		parent::__construct($source);
	}
}