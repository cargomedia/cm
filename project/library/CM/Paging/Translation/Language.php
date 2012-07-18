<?php

class CM_Paging_Translation_Language extends CM_Paging_Abstract {

	/**
	 * @param CM_Model_Language              $language
	 * @param string|null                    $searchPhrase
	 * @param string|null                    $section
	 * @param bool|null                      $translated
	 */
	public function __construct(CM_Model_Language $language, $searchPhrase = null, $section = null, $translated = null) {
		$join = 'LEFT JOIN ' . TBL_CM_LANGUAGEVALUE . ' ON id = languageKeyId AND languageId = ' . $language->getId();
		$where = array();
		if ($searchPhrase) {
			$whereName = CM_Mysql::placeholder("name LIKE '?'", '%' . $searchPhrase . '%');
			$whereValue = CM_Mysql::placeholder("value LIKE '?'", '%' . $searchPhrase . '%');
			$where[] = '(' . $whereName . ' OR ' . $whereValue . ')';
		}
		if ($section) {
			$where[] = CM_Mysql::placeholder("name LIKE '?%'", $section);
		}
		if ($translated === true) {
			$where[] = 'value IS NOT NULL';
		}
		if ($translated === false) {
			$where[] = 'value IS NULL';
		}
		$where = ($where) ? join(' AND ', $where) : null;
		$source = new CM_PagingSource_Sql_Deferred('name AS `key`, value', TBL_CM_LANGUAGEKEY, $where, null, $join);
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