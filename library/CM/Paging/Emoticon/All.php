<?php

class CM_Paging_Emoticon_All extends CM_Paging_Emoticon_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('id, code, codeAdditional, file', 'cm_emoticon', null, '`id`');
		$source->enableCacheLocal();
		parent::__construct($source);
	}
}
