<?php

class CM_Model_Language extends CM_Model_Abstract {

    /**
     * @return string
     */
    public function getName() {
        return $this->_get('name');
    }

    /**
     * @return string
     */
    public function getAbbreviation() {
        return $this->_get('abbreviation');
    }

    /**
     * @return bool
     */
    public function getEnabled() {
        return $this->_get('enabled');
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled) {
        $this->_set('enabled', $enabled);
    }

    /**
     * @return CM_Model_Language|null
     */
    public function getBackup() {
        return $this->_get('backupId');
    }

    /**
     * @return CM_Paging_Translation_Language
     */
    public function getTranslations() {
        return new CM_Paging_Translation_Language($this);
    }

    /**
     * @param string     $phrase
     * @param array|null $variableNames
     * @param bool|null  $skipCacheLocal
     * @return string
     */
    public function getTranslation($phrase, array $variableNames = null, $skipCacheLocal = null) {
        $phrase = (string) $phrase;
        $cacheKey = CM_CacheConst::Language_Translations . '_languageId:' . $this->getId();
        $cache = CM_Cache_Local::getInstance();
        if ($skipCacheLocal || false === ($translations = $cache->get($cacheKey))) {
            $translations = $this->getTranslations()->getAssociativeArray();
            if (!$skipCacheLocal) {
                $cache->set($cacheKey, $translations);
            }
        }

        if (!array_key_exists($phrase, $translations)) {
            CM_Model_LanguageKey::create($phrase, $variableNames);
            $translations[$phrase] = ['value' => $phrase, 'variables' => $variableNames];
            if (!$skipCacheLocal) {
                $cache->set($cacheKey, $translations);
            }
        }

        if ($variableNames !== null) {
            sort($variableNames);
            if ($variableNames !== $translations[$phrase]['variables']) {
                $languageKey = CM_Model_LanguageKey::findByName($phrase);
                $languageKey->setVariables($variableNames);
                $translations[$phrase]['variables'] = $variableNames;
                if (!$skipCacheLocal) {
                    $cache->set($cacheKey, $translations);
                }
            }
        }

        if (!isset($translations[$phrase]['value'])) {
            if (!$this->getBackup()) {
                return $phrase;
            }
            return $this->getBackup()->getTranslation($phrase, $variableNames, $skipCacheLocal);
        }
        return $translations[$phrase]['value'];
    }

    /**
     * @param string      $phrase
     * @param string|null $value
     * @param array|null  $variables
     */
    public function setTranslation($phrase, $value = null, array $variables = null) {
        $this->getTranslations()->set($phrase, $value, $variables);
    }

    /**
     * @param CM_Model_Language|null $language
     * @throws CM_Exception_Invalid
     */
    public function setBackup(CM_Model_Language $language = null) {
        $this->_set('backupId', $language);
        $this->_change();
    }

    /**
     * @param CM_Model_Language $language
     * @return bool
     */
    public function isBackingUp(CM_Model_Language $language) {
        while (!is_null($language)) {
            if ($this->equals($language)) {
                return true;
            }
            $language = $language->getBackup();
        }
        return false;
    }

    public function toArray() {
        $array = parent::toArray();
        $array['abbreviation'] = $this->getAbbreviation();
        return $array;
    }

    protected function _getSchema() {
        return new CM_Model_Schema_Definition(array(
            'name'         => array('type' => 'string'),
            'abbreviation' => array('type' => 'string'),
            'enabled'      => array('type' => 'boolean'),
            'backupId'     => array('type' => 'CM_Model_Language', 'optional' => true),
        ));
    }

    protected function _getContainingCacheables() {
        $cacheables = parent::_getContainingCacheables();
        $cacheables[] = new CM_Paging_Language_All();
        $cacheables[] = new CM_Paging_Language_Enabled();
        return $cacheables;
    }

    protected function _onDeleteBefore() {
        CM_Db_Db::delete('cm_languageValue', array('languageId' => $this->getId()));
        /** @var CM_Model_Language $language */
        foreach (new CM_Paging_Language_All() as $language) {
            if ($this->isBackingUp($language) && !$this->equals($language)) {
                $language->setBackup(null);
            }
        }
        CM_Db_Db::update('cm_user', array('languageId' => null), array('languageId' => $this->getId()));
    }

    protected function _onDelete() {
        CM_Db_Db::delete('cm_model_language', array('id' => $this->getId()));
    }

    /**
     * @param string                 $name
     * @param string                 $abbreviation
     * @param bool                   $enabled
     * @param CM_Model_Language|null $backup
     * @return CM_Model_Language
     */
    public static function create($name, $abbreviation, $enabled, CM_Model_Language $backup = null) {
        $language = new self();
        $language->_set([
            'name'         => $name,
            'abbreviation' => $abbreviation,
            'enabled'      => $enabled,
            'backupId'     => $backup,
        ]);
        $language->commit();
        return $language;
    }

    /**
     * @param string $abbreviation
     * @return CM_Model_Language|null
     */
    public static function findByAbbreviation($abbreviation) {
        $abbreviation = (string) $abbreviation;
        $languageList = new CM_Paging_Language_All();
        return $languageList->findByAbbreviation($abbreviation);
    }

    /**
     * @return CM_Model_Language|null
     */
    public static function findDefault() {
        $cacheKey = CM_CacheConst::Language_Default;
        $cache = CM_Cache_Local::getInstance();
        if (false === ($languageId = $cache->get($cacheKey))) {
            $languageId = CM_Db_Db::select('cm_model_language', 'id', array('enabled' => true, 'backupId' => null))->fetchColumn();
            $cache->set($cacheKey, $languageId);
        }
        if (!$languageId) {
            return null;
        }
        return new static($languageId);
    }

    /**
     * @return CM_Tree_Language
     */
    public static function getTree() {
        $cacheKey = CM_CacheConst::Language_Tree;
        $cache = CM_Cache_Local::getInstance();
        if (false === ($tree = $cache->get($cacheKey))) {
            $tree = new CM_Tree_Language();
            $cache->set($cacheKey, $tree);
        }
        return $tree;
    }

    /**
     * @return int
     */
    public static function getVersionJavascript() {
        return (int) CM_Option::getInstance()->get('language.javascript.version');
    }

    public static function updateVersionJavascript() {
        CM_Option::getInstance()->set('language.javascript.version', time());
    }

    /**
     * @param string $languageKey
     * @throws CM_Exception_Invalid
     */
    public static function rpc_requestTranslationJs($languageKey) {
        $javascript = CM_Db_Db::select('cm_model_languagekey', 'javascript', array('name' => $languageKey))->fetchColumn();
        if ($javascript === false) {
            throw new CM_Exception_Invalid('Language key `' . $languageKey . '` not found');
        }
        if ($javascript == 0) {
            CM_Db_Db::update('cm_model_languagekey', array('javascript' => 1), array('name' => $languageKey));
            self::updateVersionJavascript();
        }
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_Database';
    }
}
