<?php

class CM_Paging_Translation_Language extends CM_Paging_Abstract {

    /** @var CM_Model_Language */
    protected $_language;

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
        $groupBy = 'k.name';
        $source = new CM_PagingSource_Sql_Deferred('k.name AS `key`, v.value, k.variables',
            'cm_model_languageKey` as `k', implode(' AND ', $where), $orderBy, $join, $groupBy, $parameters);
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

    /**
     * @param string $phrase
     * @throws CM_Exception_Invalid
     * @return string
     */
    public function get($phrase) {
        $translations = $this->getAssociativeArray();
        if (!array_key_exists($phrase, $translations)) {
            throw new CM_Exception_Invalid('Translation `' . $phrase . '` does not exist');
        }
        return $translations[$phrase]['value'];
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
    }

    protected function _processItem($item) {
        $item['variables'] = ($item['variables']) ? json_decode($item['variables']) : array();
        sort($item['variables']);
        return $item;
    }

    public function _change() {
        parent::_change();
        $cacheKey = CM_CacheConst::Language_Translations . '_languageId:' . $this->_language->getId();
        CM_Cache_Local::getInstance()->delete($cacheKey);
    }
}
