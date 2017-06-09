<?php

class CM_Paging_Translation_Language_All extends CM_Paging_Translation_Language_Abstract {

    /** @var boolean */
    private $_javascriptOnly;

    /**
     * @param CM_Model_Language $language
     * @param bool|null         $javascriptOnly
     */
    public function __construct(CM_Model_Language $language, $javascriptOnly = null) {
        $this->_language = $language;
        $this->_javascriptOnly = (boolean) $javascriptOnly;
        $where = null;
        if ($javascriptOnly) {
            $where = 'k.javascript = 1';

            // Include the javascript version in the cache key, so the paging invalidates when the version changes.
            $javascriptVersion = CM_Model_Language::getVersionJavascript();
            $where .= " AND '{$javascriptVersion}' = '{$javascriptVersion}'";
        }
        $orderBy = 'k.name ASC';
        $join = 'LEFT JOIN `cm_languageValue` AS v ON k.id = v.languageKeyId AND v.languageId = ' . $this->_language->getId() . ' ';
        $source = new CM_PagingSource_Sql_Deferred('k.name AS `key`, v.value, k.variables', 'cm_model_languagekey` as `k', $where, $orderBy, $join);
        $source->enableCache(null, CM_Cache_Persistent::getInstance());
        parent::__construct($source);
    }

    /**
     * @param string      $phrase
     * @param string|null $value
     * @param array|null  $variables
     */
    public function set($phrase, $value = null, array $variables = null) {
        if (null === $value) {
            $value = $phrase;
        }

        $languageKey = CM_Model_LanguageKey::replace($phrase, $variables);
        CM_Db_Db::insert('cm_languageValue', array(
            'value'         => $value,
            'languageKeyId' => $languageKey->getId(),
            'languageId'    => $this->_language->getId()
        ), null, array('value' => $value));
        $this->_change();
        (new self($this->_language, !$this->_javascriptOnly))->_change();
    }

    /**
     * @param string $phrase
     */
    public function remove($phrase) {
        $languageKey = CM_Model_LanguageKey::findByName($phrase);
        if (!$languageKey) {
            return;
        }
        CM_Db_Db::delete('cm_languageValue', array('languageKeyId' => $languageKey->getId(), 'languageId' => $this->_language->getId()));
        $this->_change();
        (new self($this->_language, !$this->_javascriptOnly))->_change();
    }

    public function _change() {
        parent::_change();
        $this->getItemsRaw(); // eagerly refill the cache
    }

}
