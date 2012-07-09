<?php

class CM_Paging_Translation_Language extends CM_Paging_Abstract {

	const TYPE_ALL = 1;
	const TYPE_TRANSLATED = 2;
	const TYPE_UNTRANSLATED = 3;

	/**
	 * @param CM_Model_Language         $language
	 * @param string|null               $searchPhrase
	 * @param integer|null              $type
	 * @throws CM_Exception_InvalidParam
	 */
	public function __construct(CM_Model_Language $language, $searchPhrase = null, $translated = null) {
		$join = 'LEFT JOIN ' . TBL_CM_LANGUAGEVALUE . ' ON id = languageKeyId AND languageId = ' . $language->getId();
		$where = array();
		if ($searchPhrase) {
			$where[] = CM_Mysql::placeholder("name LIKE '?'", '%' . $searchPhrase . '%');
		}
		if ($translated === true) {
			$where[] = 'value IS NOT NULL';
		}
		if ($translated === false) {
			$where[] = 'value IS NULL';
		}
		$where = ($where) ? join(' AND ', $where) : null;
		$source = new CM_PagingSource_Sql_Deferred('name AS `key`, value', TBL_CM_LANGUAGEKEY, $where, null, $join);
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