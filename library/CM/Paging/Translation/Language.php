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
		$parameters = array();
		if ($searchPhrase) {
			$where[] = '(k.name LIKE ? OR v.value LIKE ?)';
			$parameters[] = '%' . $searchPhrase . '%';
			$parameters[] = '%' . $searchPhrase . '%';
		}
		if ($section) {
			$where[] = 'k.name LIKE ?';
			$parameters[] = $section . '%';
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

		$orderBy = 'k.name ASC';
		$join = 'LEFT JOIN `cm_languageValue` AS v ON k.id = v.languageKeyId AND v.languageId = ' . $language->getId() . ' ';
		$join .= 'LEFT JOIN `cm_languageKey_variable` AS kv ON k.id = kv.languageKeyId';
		$groupBy = 'k.name';
		$source = new CM_PagingSource_Sql_Deferred('k.name AS `key`, v.value, GROUP_CONCAT(kv.name SEPARATOR ",") as variables',
				'cm_languageKey` as `k', implode(' AND ', $where), $orderBy, $join, $groupBy, $parameters);
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
