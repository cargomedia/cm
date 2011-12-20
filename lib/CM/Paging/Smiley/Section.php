<?php

class CM_Paging_Smiley_Section extends CM_Paging_Smiley_Abstract {

	/**
	 * @param int $section
	 */
	public function __construct($section) {
		$section = (int) $section;
		$source = new CM_PagingSource_Sql('id, section, file, code', TBL_CM_SMILEY, '`section`=' . $section, '`id`');
		$source->enableCacheLocal();
		parent::__construct($source);
	}
}
