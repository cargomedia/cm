<?php

class CM_Paging_Language_All extends CM_Paging_Language_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('id', TBL_CM_LANGUAGE);
		$source->enableCache();
		parent::__construct($source);
	}

}