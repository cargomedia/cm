<?php

class CM_Paging_User_Online extends CM_Paging_User_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('userId', 'cm_user_online');

		parent::__construct($source);
	}
}
