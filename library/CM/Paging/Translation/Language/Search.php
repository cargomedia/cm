<?php

class CM_Paging_Translation_Language_Search extends CM_Paging_Translation_Language_Abstract {
    
    /**
     * @param CM_Model_Language $language
     * @param string|null       $searchPhrase
     * @param string|null       $section
     * @param bool|null         $translated
     * @param bool|null         $javascriptOnly
     */
    public function __construct(CM_Model_Language $language, $searchPhrase = null, $section = null, $translated = null, $javascriptOnly = null) {
        $this->_language = $language;
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
        $join = 'LEFT JOIN `cm_languageValue` AS v ON k.id = v.languageKeyId AND v.languageId = ' . $this->_language->getId() . ' ';
        $groupBy = 'BINARY k.name';
        $source = new CM_PagingSource_Sql_Deferred('k.name AS `key`, v.value, k.variables',
            'cm_model_languagekey` as `k', implode(' AND ', $where), $orderBy, $join, $groupBy, $parameters);
        parent::__construct($source);
    }
}