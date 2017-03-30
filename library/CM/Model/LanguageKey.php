<?php

class CM_Model_LanguageKey extends CM_Model_Abstract {

    const MAX_UPDATE_COUNT = 50;

    /**
     * @return string
     */
    public function getName() {
        return $this->_get('name');
    }

    /**
     * @return string $name
     */
    public function setName($name) {
        $this->_set('name', $name);
        $this->_changeContainingCacheables();
    }

    /**
     * @return string[]
     */
    public function getVariables() {
        $variablesEncoded = $this->_get('variables');
        return CM_Params::jsonDecode($variablesEncoded);
    }

    /**
     * @param string[]|null $variables
     * @throws CM_Exception_Invalid
     */
    public function setVariables(array $variables = null) {
        $previousVariables = $this->getVariables();
        $variables = (array) $variables;
        $variables = array_values($variables);
        sort($variables);
        if ($previousVariables !== $variables) {
            $variablesEncoded = CM_Params::jsonEncode($variables);
            $this->_set('variables', $variablesEncoded);

            $this->_increaseUpdateCount();
            $this->_changeContainingCacheables();
            if ($this->_getUpdateCount() > self::MAX_UPDATE_COUNT) {
                $message = [
                    'Variables for languageKey `' . $this->getName() . '` have been updated over ' . self::MAX_UPDATE_COUNT . ' times since release.',
                    'Previous variables: `' . CM_Util::var_line($previousVariables) . '`',
                    'Current variables: `' . CM_Util::var_line($variables) . '`',
                ];
                throw new CM_Exception_Invalid(join(PHP_EOL, $message));
            }
        }
    }

    /**
     * @return bool
     */
    public function getJavascript() {
        return $this->_get('javascript');
    }

    public function enableJavascript() {
        $this->_set('javascript', true);
    }

    /**
     * @return int
     */
    protected function _getUpdateCount() {
        if ($this->_getDeployVersion() > $this->_get('updateCountResetVersion')) {
            return 0;
        }
        return $this->_get('updateCount');
    }

    protected function _increaseUpdateCount() {
        $data = [
            'updateCount' => $this->_getUpdateCount() + 1
        ];
        if ($this->_getDeployVersion() > $this->_get('updateCountResetVersion')) {
            $data['updateCountResetVersion'] = $this->_getDeployVersion();
        }
        $this->_set($data);
    }

    /**
     * @return int
     */
    protected function _getDeployVersion() {
        return CM_App::getInstance()->getDeployVersion();
    }

    protected function _getSchema() {
        return new CM_Model_Schema_Definition(array(
            'name'                    => array('type' => 'string'),
            'variables'               => array('type' => 'string'),
            'updateCountResetVersion' => array('type' => 'int', 'optional' => true),
            'updateCount'             => array('type' => 'int'),
            'javascript'              => array('type' => 'bool'),
        ));
    }

    protected function _onDeleteBefore() {
        CM_Db_Db::delete('cm_languageValue', array('languageKeyId' => $this->getId()));
    }

    protected function _getContainingCacheables() {
        $languages = new CM_Paging_Language_All();
        return array_flatten(\Functional\map($languages, function (CM_Model_Language $language) {
            return [$language->getTranslations(false), $language->getTranslations(true)];
        }));
    }

    protected function _changeContainingCacheables() {
        parent::_changeContainingCacheables();
        CM_Cache_Local::getInstance()->delete(CM_CacheConst::LanguageKey_Tree);
    }

    /**
     * @param string     $name
     * @param array|null $variables
     * @return CM_Model_LanguageKey
     */
    public static function create($name, array $variables = null) {
        $languageKey = new self();
        $variables = (array) $variables;
        $languageKey->_set([
            'name'                    => $name,
            'updateCount'             => 0,
            'updateCountResetVersion' => 0,
            'javascript'              => false,
            'variables'               => CM_Util::jsonEncode($variables),
        ]);
        $languageKey->commit();

        $languageKey = self::_replaceWithExisting($languageKey);
        return $languageKey;
    }

    /**
     * @param string $name
     * @return CM_Model_LanguageKey|null
     */
    public static function findByName($name) {
        $name = (string) $name;
        $languageKeyId = CM_Db_Db::select('cm_model_languagekey', 'id', array('name' => $name), 'id ASC')->fetchColumn();
        if (!$languageKeyId) {
            return null;
        }
        return new self($languageKeyId);
    }

    /**
     * @param string     $name
     * @param array|null $variableNames
     * @return self
     */
    public static function replace($name, array $variableNames = null) {
        $languageKey = self::findByName($name);
        if (!$languageKey) {
            $languageKey = self::create($name, $variableNames);
        } elseif (null !== $variableNames) {
            $languageKey->setVariables($variableNames);
        }
        return $languageKey;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function exists($name) {
        $name = (string) $name;
        return (boolean) CM_Db_Db::count('cm_model_languagekey', array('name' => $name));
    }

    /**
     * @param string $name
     */
    public static function deleteByName($name) {
        $languageKey = self::findByName($name);
        if ($languageKey) {
            $languageKey->delete();
        }
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_Database';
    }

    /**
     * @return CM_Tree_Language
     */
    public static function getTree() {
        $cacheKey = CM_CacheConst::LanguageKey_Tree;
        $cache = CM_Cache_Local::getInstance();
        if (false === ($tree = $cache->get($cacheKey))) {
            $tree = new CM_Tree_Language();
            $cache->set($cacheKey, $tree);
        }
        return $tree;
    }

    /**
     * @param CM_Model_LanguageKey $languageKey
     * @return CM_Model_LanguageKey
     */
    protected static function _replaceWithExisting(CM_Model_LanguageKey $languageKey) {
        $name = $languageKey->getName();
        $languageKeyIdList = CM_Db_Db::select('cm_model_languagekey', 'id', array('name' => $name), 'id ASC')->fetchAllColumn();

        if (count($languageKeyIdList) > 1) {
            $languageKeyId = array_shift($languageKeyIdList);
            CM_Db_Db::exec("DELETE FROM `cm_model_languagekey` WHERE `name` = ? AND `id` != ?", array($name, $languageKeyId));
            $languageKey = new self($languageKeyId);
        }
        return $languageKey;
    }
}
