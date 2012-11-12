<?php

class CM_Paging_Splitfeature_All extends CM_Paging_Splitfeature_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('name', TBL_CM_SPLITFEATURE);
		parent::__construct($source);
	}
}
