<?php

class CM_Model_Language extends CM_Model_Abstract {

    /** @var CM_Model_Language|null $_backup */
    private $_backup;

    /**
     * @return string
     */
    public function getName() {
        return (string) $this->_get('name');
    }

    /**
     * @return string
     */
    public function getAbbreviation() {
        return (string) $this->_get('abbreviation');
    }

    /**
     * @return bool
     */
    public function getEnabled() {
        return (bool) $this->_get('enabled');
    }

    /**
     * @return CM_Paging_Translation_Language
     */
    public function getTranslations() {
        return new CM_Paging_Translation_Language($this);
    }

    /**
     * @param string $phrase
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

        // Check if translation exists and if variables provided match the ones in database
        if (!array_key_exists($phrase, $translations)) {
            static::_setKey($phrase, $variableNames);
            $this->_change();
        } elseif ($variableNames !== null) {
            sort($variableNames);
            if ($variableNames !== $translations[$phrase]['variables']) {
                static::_setKey($phrase, $variableNames);
                $this->_change();
            }
        }
        // Getting value from backup language if backup is present and value does not exist
        if (!isset($translations[$phrase]['value'])) {
            if (!$this->getBackup()) {
                return $phrase;
            }
            return $this->getBackup()->getTranslation($phrase, $variableNames, $skipCacheLocal);
        }
        return $translations[$phrase]['value'];
    }

    /**
     * @param string $phrase
     * @param string|null $value
     * @param array|null  $variables
     */
    public function setTranslation($phrase, $value = null, array $variables = null) {
        if (null === $value) {
            $value = $phrase;
        }

        $languageKeyId = static::_setKey($phrase, $variables);
        CM_Db_Db::insert('cm_languageValue',
            array('value' => $value, 'languageKeyId' => $languageKeyId, 'languageId' => $this->getId()), null, array('value' => $value));
        $this->_change();
    }

    /**
     * @param string $phrase
     */
    public function unsetTranslation($phrase) {
        $languageKeyId = static::_setKey($phrase);
        CM_Db_Db::delete('cm_languageValue', array('languageKeyId' => $languageKeyId, 'languageId' => $this->getId()));
        $this->_change();
    }

    /**
     * @param string                 $name
     * @param string                 $abbreviation
     * @param bool|null              $enabled
     * @param CM_Model_Language|null $backup
     */
    public function setData($name, $abbreviation, $enabled = null, CM_Model_Language $backup = null) {
        $name = (string) $name;
        $abbreviation = (string) $abbreviation;
        $enabled = (bool) $enabled;
        $backupId = ($backup) ? $backup->getId() : null;
        CM_Db_Db::update('cm_language', array('name'     => $name, 'abbreviation' => $abbreviation, 'enabled' => $enabled,
                                              'backupId' => $backupId), array('id' => $this->getId()));
        $this->_change();
    }

    /**
     * @return CM_Model_Language|null
     */
    public function getBackup() {
        if (!$this->_backup && $this->_get('backupId')) {
            $this->_backup = new CM_Model_Language($this->_get('backupId'));
        }
        return $this->_backup;
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

    protected function _loadData() {
        return CM_Db_Db::select('cm_language', '*', array('id' => $this->getId()))->fetch();
    }

    protected function _onChange() {
        $cacheKey = CM_CacheConst::Language_Translations . '_languageId:' . $this->getId();
        CM_Cache_Local::getInstance()->delete($cacheKey);
    }

    protected function _getContainingCacheables() {
        $cacheables = parent::_getContainingCacheables();
        $cacheables[] = new CM_Paging_Language_All();
        $cacheables[] = new CM_Paging_Language_Enabled();
        return $cacheables;
    }

    protected function _onDeleteBefore() {
        CM_Db_Db::delete('cm_languageValue', array('languageId' => $this->getId()));
        CM_Db_Db::update('cm_language', array('backupId' => null), array('backupId' => $this->getId()));
        CM_Db_Db::update('cm_user', array('languageId' => null), array('languageId' => $this->getId()));
    }

    protected function _onDelete() {
        CM_Db_Db::delete('cm_language', array('id' => $this->getId()));
    }

    protected function _onDeleteAfter() {
        self::changeAll();
    }

    /**
     * @param string $name
     * @param string $abbreviation
     * @param bool   $enabled
     * @return static
     */
    public static function create($name, $abbreviation, $enabled) {
        return CM_Model_Language::createStatic(array(
            'name'         => (string) $name,
            'abbreviation' => (string) $abbreviation,
            'enabled'      => (bool) $enabled,
        ));
    }

    public static function changeAll() {
        /** @var CM_Model_Language $language */
        foreach (new CM_Paging_Language_All() as $language) {
            $language->_change();
        }
    }

    /**
     * @param string $abbreviation
     * @return CM_Model_Language|null
     */
    public static function findByAbbreviation($abbreviation) {
        $abbreviation = (string) $abbreviation;
        $languageId = CM_Db_Db::select('cm_language', 'id', array('abbreviation' => $abbreviation))->fetchColumn();
        if (!$languageId) {
            return null;
        }
        return new static($languageId);
    }

    /**
     * @return CM_Model_Language|null
     */
    public static function findDefault() {
        $cacheKey = CM_CacheConst::Language_Default;
        $cache = CM_Cache_Local::getInstance();
        if (false === ($languageId = $cache->get($cacheKey))) {
            $languageId = CM_Db_Db::select('cm_language', 'id', array('enabled' => true, 'backupId' => null))->fetchColumn();
            $cache->set($cacheKey, $languageId);
        }
        if (!$languageId) {
            return null;
        }
        return new static($languageId);
    }

    /**
     * @param string $phrase
     */
    public static function deleteKey($phrase) {
        $phrase = (string) $phrase;
        $languageKeyId = CM_Db_Db::select('cm_languageKey', 'id', array('name' => $phrase))->fetchColumn();
        if (!$languageKeyId) {
            return;
        }
        CM_Db_Db::delete('cm_languageValue', array('languageKeyId' => $languageKeyId));
        CM_Db_Db::delete('cm_languageKey', array('id' => $languageKeyId));
        self::changeAll();
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
     * @param string $phrase
     * @return boolean
     */
    public static function hasKey($phrase) {
        $phrase = (string) $phrase;
        return (boolean) CM_Db_Db::count('cm_languageKey', array('name' => $phrase));
    }

    /**
     * @param string $phrase
     * @param string|null $nameNew
     * @param array|null  $variableNamesNew
     * @throws CM_Exception_Nonexistent
     * @throws CM_Exception_Duplicate
     */
    public static function updateKey($phrase, $nameNew = null, array $variableNamesNew = null) {
        $phrase = (string) $phrase;
        if (!CM_Db_Db::count('cm_languageKey', array('name' => $phrase))) {
            throw new CM_Exception_Nonexistent('LanguageKey `' . $phrase . '` does not exist');
        }

        if ($variableNamesNew !== null) {
            $variableNamesNew = json_encode($variableNamesNew);
        }

        if (null !== $nameNew) {
            $nameNew = (string) $nameNew;
            if (CM_Db_Db::count('cm_languageKey', array('name' => $nameNew))) {
                throw new CM_Exception_Duplicate('LanguageKey `' . $nameNew . '` already exists');
            }

            CM_Db_Db::update('cm_languageKey', array('name' => $nameNew, 'variables' => $variableNamesNew), array('name' => $phrase));
        } else {
            CM_Db_Db::update('cm_languageKey', array('variables' => $variableNamesNew), array('name' => $phrase));
        }
        self::changeAll();
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
        $javascript = CM_Db_Db::select('cm_languageKey', 'javascript', array('name' => $languageKey))->fetchColumn();
        if ($javascript === false) {
            throw new CM_Exception_Invalid('Language key `' . $languageKey . '` not found');
        }
        if ($javascript == 0) {
            CM_Db_Db::update('cm_languageKey', array('javascript' => 1), array('name' => $languageKey));
            self::updateVersionJavascript();
        }
    }

    protected static function _createStatic(array $data) {
        $params = CM_Params::factory($data);
        $backupId = ($params->has('backup')) ? $params->getLanguage('backup')->getId() : null;
        $id = CM_Db_Db::insert('cm_language', array(
            'name'         => $params->getString('name'),
            'abbreviation' => $params->getString('abbreviation'),
            'enabled'      => $params->getBoolean('enabled'),
            'backupId'     => $backupId,
        ));
        return new static($id);
    }

    /**
     * @param string $phrase
     * @param array|null $variableNames
     * @return int
     */
    private static function _setKey($phrase, array $variableNames = null) {
        $phrase = (string) $phrase;
        $languageKeyId = CM_Db_Db::select('cm_languageKey', 'id', array('name' => $phrase), 'id ASC')->fetchColumn();
        if (!$languageKeyId) {
            $languageKeyId = CM_Db_Db::insert('cm_languageKey', array('name' => $phrase));

            // check if the language Key is double inserted because of high load
            $languageKeyIdList = CM_Db_Db::select('cm_languageKey', 'id', array('name' => $phrase), 'id ASC')->fetchAllColumn();
            if (1 < count($languageKeyIdList)) {
                $languageKeyId = array_shift($languageKeyIdList);
                CM_Db_Db::exec("DELETE FROM `cm_languageKey` WHERE `name` = ? AND `id` != ?", array($phrase, $languageKeyId));
            }

            self::changeAll();
        }
        if (null !== $variableNames) {
            self::_setKeyVariables($phrase, $variableNames);
        }
        return $languageKeyId;
    }

    /**
     * @param string $phrase
     * @param array  $variableNames
     * @throws CM_Exception_Invalid
     * @throws CM_Exception_Duplicate
     */
    private static function _setKeyVariables($phrase, array $variableNames) {
        $languageKeyParams = CM_Db_Db::select('cm_languageKey', array('id', 'updateCountResetVersion', 'updateCount'),
            array('name' => $phrase))->fetch();
        if (!$languageKeyParams) {
            throw new CM_Exception_Invalid('Language key `' . $phrase . '` was not found');
        }
        $languageKeyId = $languageKeyParams['id'];
        $updateCount = $languageKeyParams['updateCount'] + 1;
        $deployVersion = CM_App::getInstance()->getDeployVersion();
        if ($deployVersion > $languageKeyParams['updateCountResetVersion']) {
            $updateCount = 1;
        }
        CM_Db_Db::update('cm_languageKey', array('updateCountResetVersion' => $deployVersion,
                                                 'updateCount'             => $updateCount), array('name' => $phrase));
        if ($updateCount > 50) {
            throw new CM_Exception_Invalid('Variables for languageKey `' . $phrase . '` have been already updated over 50 times since release');
        }

        if (count($variableNames) !== count(array_unique($variableNames))) {
            throw new CM_Exception_Duplicate('Duplicate variable name declaration `' . json_encode($variableNames) . '`');
        }

        CM_Db_Db::update('cm_languageKey', array('variables' => json_encode($variableNames)), array('id' => $languageKeyId));

        self::changeAll();
    }
}
