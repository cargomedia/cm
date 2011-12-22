<?php

class CM_Paging_Smiley_Set extends CM_Paging_Smiley_Abstract {

	/**
	 * @param int $setId
	 */
	public function __construct($setId) {
		$setId = (int) $setId;
		$source = new CM_PagingSource_Sql('id, setId, file, code', TBL_CM_SMILEY, '`setId`=' . $setId, '`id`');
		$source->enableCacheLocal();
		parent::__construct($source);
	}
}
