<?php

class CM_Paging_SmileySet_All extends CM_Paging_SmileySet_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('id', TBL_CM_SMILEYSET);
		$source->enableCacheLocal();
		parent::__construct($source);
	}
}
