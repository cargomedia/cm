<?php

class CM_Model_Language extends CM_Model_Abstract {

    /**
     * @return string
     */
    public function getName() {
        return $this->_get('name');
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->_set('name', $name);
    }

    /**
     * @return string
     */
    public function getAbbreviation() {
        return $this->_get('abbreviation');
    }

    /**
     * @param string $abbreviation
     */
    public function setAbbreviation($abbreviation) {
        $this->_set('abbreviation', $abbreviation);
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
     * @param CM_Model_Language|null $language
     */
    public function setBackup(CM_Model_Language $language = null) {
        $this->_set('backupId', $language);
    }

    /**
     * @param CM_Model_Language $language
     * @return bool
     */
    public function isBackingUp(CM_Model_Language $language) {
        while (null !== $language) {
            $language = $language->getBackup();
            if ($this->equals($language)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param boolean|null $javascriptOnly
     * @return CM_Paging_Translation_Language_All
     */
    public function getTranslations($javascriptOnly = null) {
        return new CM_Paging_Translation_Language_All($this, $javascriptOnly);
    }

    /**
     * @param string     $phrase
     * @param array|null $variableNames
     * @param bool|null  $skipCacheLocal
     * @return string
     */
    public function getTranslation($phrase, array $variableNames = null, $skipCacheLocal = null) {
        $writeCache = false;
        $phrase = (string) $phrase;
        $cacheKey = CM_CacheConst::Language_Translations . '_languageId:' . $this->getId();
        $cache = CM_Cache_Local::getInstance();
        if ($skipCacheLocal || false === ($translations = $cache->get($cacheKey))) {
            $translations = $this->getTranslations()->getAssociativeArray();
            $writeCache = true;
        }

        if (!array_key_exists($phrase, $translations)) {
            CM_Model_LanguageKey::create($phrase, $variableNames);
            $translations[$phrase] = ['value' => $phrase, 'variables' => $variableNames];
            $writeCache = true;
        }

        if ($variableNames !== null) {
            sort($variableNames);
            if ($variableNames !== $translations[$phrase]['variables']) {
                $languageKey = CM_Model_LanguageKey::findByName($phrase);
                $languageKey->setVariables($variableNames);
                $translations[$phrase]['variables'] = $variableNames;
                $writeCache = true;
            }
        }

        if ($writeCache && !$skipCacheLocal) {
            $cache->set($cacheKey, $translations, $this->_getConfig()->cacheLifetime);
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

    public function jsonSerialize() {
        $array = parent::jsonSerialize();
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
     * @return int
     */
    public static function getVersionJavascript() {
        return (int) CM_Service_Manager::getInstance()->getOptions()->get('language.javascript.version');
    }

    public static function refreshCaches() {
        $cache = CM_Cache_Local::getInstance();
        /** @var CM_Model_Language $language */
        foreach (new CM_Paging_Language_All() as $language) {
            $cacheKey = CM_CacheConst::Language_Translations . '_languageId:' . $language->getId();
            $translations = $language->getTranslations();
            $translations->_change();
            $cache->set($cacheKey, $translations->getAssociativeArray(), $language->_getConfig()->cacheLifetime);
        }
        $language->getTranslations(true)->_change();
    }

    public static function updateVersionJavascript() {
        CM_Service_Manager::getInstance()->getOptions()->set('language.javascript.version', time());
    }

    /**
     * @param string $phrase
     * @throws CM_Exception_Invalid
     */
    public static function rpc_requestTranslationJs($phrase) {
        $languageKey = CM_Model_LanguageKey::findByName($phrase);
        if (!$languageKey) {
            throw new CM_Exception_Invalid('Language key not found', null, ['phrase' => $phrase]);
        }
        if (!$languageKey->getJavascript()) {
            $languageKey->enableJavascript();
            self::updateVersionJavascript();
        }
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_Database';
    }
}
