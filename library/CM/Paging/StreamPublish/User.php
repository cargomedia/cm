<?php

class CM_Paging_StreamPublish_User extends CM_Paging_StreamPublish_Abstract {

	/**
	 * @param CM_Model_User $user
	 */
	public function __construct(CM_Model_User $user) {
		$source = new CM_PagingSource_Sql('id', TBL_CM_STREAM_PUBLISH, '`userId` = ' . $user->getId());
		parent::__construct($source);
	}

}
