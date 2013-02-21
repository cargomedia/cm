<?php

class CM_Paging_Emoticon_All extends CM_Paging_Emoticon_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('id, setId, file, code', TBL_CM_EMOTICON, null, '`setId`,`id`');
		$source->enableCacheLocal();
		parent::__construct($source);
	}
}
