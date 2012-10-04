<?php

class CM_Paging_SplittestVariation_SplittestEnabled extends CM_Paging_SplittestVariation_Abstract {

	/**
	 * @param CM_Model_Splittest $splittest
	 */
	public function __construct(CM_Model_Splittest $splittest) {
		$source = new CM_PagingSource_Sql('id', TBL_CM_SPLITTESTVARIATION, '`splittestId`=' . $splittest->getId() . ' AND `enabled`=1');
		$source->enableCache();
		parent::__construct($source);
	}
}
