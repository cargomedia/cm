<?php

class CM_Paging_Translation_Language extends CM_Paging_Abstract {

	public function __construct(CM_Model_Language $language) {
		$join = 'LEFT JOIN ' . TBL_CM_LANGUAGEVALUE . ' ON id = languageKeyId AND languageId = ' . $language->getId();
		$source = new CM_PagingSource_Sql_Deferred('name AS `key`, value', TBL_CM_LANGUAGEKEY, null, null, $join);
		$source->enableCache();
		parent::__construct($source);
	}

	/**
	 * @return string[]
	 */
	public function getAssociativeArray() {
		$translations = array();
		foreach ($this as $translation) {
			$translations[$translation['key']] = $translation['value'];
		}
		return $translations;
	}

}