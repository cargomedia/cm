<?php

class CM_Paging_Smiley_All extends CM_Paging_Smiley_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('id, setId, file, code', TBL_CM_SMILEY, null, '`setId`,`id`');
		$source->enableCacheLocal();
		parent::__construct($source);
	}
}
