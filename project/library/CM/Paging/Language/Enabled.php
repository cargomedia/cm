<?php

class CM_Paging_Language_Enabled extends CM_Paging_Language_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('id, abbreviation', TBL_CM_LANGUAGE, array('enabled' => true));
		$source->enableCacheLocal();
		parent::__construct($source);
	}
}