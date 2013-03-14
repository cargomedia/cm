<?php

class CM_Paging_Translation_Language extends CM_Paging_Abstract {

	/**
	 * @param CM_Model_Language $language
	 * @param string|null       $searchPhrase
	 * @param string|null       $section
	 * @param bool|null         $translated
	 * @param bool|null         $javascriptOnly
	 */
	public function __construct(CM_Model_Language $language, $searchPhrase = null, $section = null, $translated = null, $javascriptOnly = null) {
		$where = array();
		$parameter = array();
		if ($searchPhrase) {
			$where[] = '(k.name LIKE ? OR v.value LIKE ?)';
			$parameter[] = '%' . $searchPhrase . '%';
			$parameter[] = '%' . $searchPhrase . '%';
		}
		if ($section) {
			$where[] = 'k.name LIKE ?';
			$parameter[] = $section . '%';
		}
		if ($translated === true) {
			$where[] = 'v.value IS NOT NULL';
		}
		if ($translated === false) {
			$where[] = 'v.value IS NULL';
		}
		if ($javascriptOnly) {
			$where[] = 'k.javascript = 1';
		}
		if (!$where) {
			$where = null;
			$parameter = null;
		} else {
			$where = join(' AND ', $where);
		}
		$orderBy = 'k.name ASC';
		$join = 'LEFT JOIN ' . TBL_CM_LANGUAGEVALUE . ' AS v ON k.id = v.languageKeyId AND v.languageId = ' . $language->getId() . ' ';
		$join .= 'LEFT JOIN ' . TBL_CM_LANGUAGEKEY_VARIABLE . ' AS kv ON k.id = kv.languageKeyId';
		$groupBy = 'k.name';
		$source = new CM_PagingSource_Sql_Deferred('k.name AS `key`, v.value, GROUP_CONCAT(kv.name SEPARATOR ",") as variables',
				TBL_CM_LANGUAGEKEY . '` as `k', $where, $orderBy, $join, $groupBy, $parameter);
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
		sort($item['variables']);
		return $item;
	}
}
