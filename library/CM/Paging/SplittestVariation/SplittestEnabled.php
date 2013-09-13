<?php

class CM_Paging_SplittestVariation_SplittestEnabled extends CM_Paging_SplittestVariation_Abstract {

	/**
	 * @param CM_Model_Splittest $splittest
	 */
	public function __construct(CM_Model_Splittest $splittest) {
		$source = new CM_PagingSource_Sql('id', 'cm_splittestVariation', '`splittestId`=' . $splittest->getId() . ' AND `enabled`=1');
		$source->enableCache();
		parent::__construct($source);
	}
}
