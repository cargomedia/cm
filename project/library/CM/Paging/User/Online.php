<?php

class CM_Paging_User_Online extends CM_Paging_User_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('userId', TBL_CM_USER_ONLINE);

		parent::__construct($source);
	}
}
