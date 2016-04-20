<?php

class CM_Paging_Translation_Language_All extends CM_Paging_Translation_Language_Abstract {

    // TODO: consider removing javascriptOnly option and using a languagekey paging instead

    /**
     * @param CM_Model_Language $language
     * @param bool|null         $javascriptOnly
     */
    public function __construct(CM_Model_Language $language, $javascriptOnly = null) {
        $this->_language = $language;
        $where = array();
        if ($javascriptOnly) {
            $where[] = 'k.javascript = 1';
        }
        $orderBy = 'k.name ASC';
        $join = 'LEFT JOIN `cm_languageValue` AS v ON k.id = v.languageKeyId AND v.languageId = ' . $this->_language->getId() . ' ';
        $groupBy = 'BINARY k.name';
        $source = new CM_PagingSource_Sql_Deferred('k.name AS `key`, v.value, k.variables',
            'cm_model_languagekey` as `k', implode(' AND ', $where), $orderBy, $join, $groupBy);
        parent::__construct($source);
    }

}
