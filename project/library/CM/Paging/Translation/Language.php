<?php

class CM_Paging_Translation_Language extends CM_Paging_Abstract {

	/**
	 * @param CM_Model_Language              $language
	 * @param string|null                    $searchPhrase
	 * @param string|null                    $section
	 * @param bool|null                      $translated
	 */
	public function __construct(CM_Model_Language $language, $searchPhrase = null, $section = null, $translated = null) {
		$where = array();
		if ($searchPhrase) {
			$whereName = CM_Mysql::placeholder("k.name LIKE '?'", '%' . $searchPhrase . '%');
			$whereValue = CM_Mysql::placeholder("v.value LIKE '?'", '%' . $searchPhrase . '%');
			$where[] = '(' . $whereName . ' OR ' . $whereValue . ')';
		}
		if ($section) {
			$where[] = CM_Mysql::placeholder("k.name LIKE '?%'", $section);
			$where[] = CM_Mysql::placeholder("name LIKE '?'", $section . '%');
		}
		if ($translated === true) {
			$where[] = 'v.value IS NOT NULL';
		}
		if ($translated === false) {
			$where[] = 'v.value IS NULL';
		}
		$where = ($where) ? join(' AND ', $where) : null;
		$join = 'LEFT JOIN ' . TBL_CM_LANGUAGEVALUE . ' AS v ON k.id = v.languageKeyId AND v.languageId = ' . $language->getId() . ' ';
		$join .= 'LEFT JOIN ' . TBL_CM_LANGUAGEKEY_VARIABLE . ' AS kv ON k.id = kv.languageKeyId';
		$groupBy = 'k.name';
		$source = new CM_PagingSource_Sql_Deferred('k.name AS `key`, v.value, GROUP_CONCAT(kv.name SEPARATOR ",") as variables',
				TBL_CM_LANGUAGEKEY . '` as `k', $where, null, $join, $groupBy);
		$source->getItems();
		parent::__construct($source);
	}

	/**
	 * @return string[]
	 */
	public function getAssociativeArray() {
		$translations = array();
		foreach ($this as $translation) {
			$key = $translation['key'];
			unset($translation['key']);
			$translations[$key] = $translation;
		}
		return $translations;
	}

	protected function _processItem($item) {
		$item['variables'] = ($item['variables']) ? explode(',', $item['variables']) : array();
		return $item;
	}

}