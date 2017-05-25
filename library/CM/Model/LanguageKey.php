<?php

class CM_Model_LanguageKey extends CM_Model_Abstract {

    const MAX_UPDATE_COUNT = 50;

    /**
     * @param string $name
     * @throws CM_Exception_Invalid
     */
    public function changeName($name) {
        $transaction = new \CM\Transactions\Transaction();
        $oldName = $this->getName();
        $oldHash = $this->getNameHash();
        $this->setName($name);
        $transaction->addRollback(function() use ($oldName, $oldHash) {
            $this->setNameHash($oldHash);
            $this->setName($oldName);
        });
        try {
            $this->setNameHash(self::calculateHash($name));
        } catch (CM_Exception_Invalid $e) {
            $transaction->rollback();
            throw $e;
        }
    }

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
     * @return string
     */
    public function getNameHash() {
        return $this->_get('nameHash');
    }

    /**
     * @param string $hash
     * @throws CM_Exception_Invalid
     */
    public function setNameHash($hash) {
        try {
            return $this->_set('nameHash', $hash);
        } catch (CM_Db_Exception $e) {
            throw new CM_Exception_Invalid('Duplicate languageKey name-hash', null, ['hash' => $hash]);
        }
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
        return new CM_Model_Schema_Definition([
            'name'                    => ['type' => 'string'],
            'variables'               => ['type' => 'string'],
            'updateCountResetVersion' => ['type' => 'int', 'optional' => true],
            'updateCount'             => ['type' => 'int'],
            'javascript'              => ['type' => 'bool'],
            'nameHash'                => ['type' => 'string'],
        ]);
    }

    protected function _onDeleteBefore() {
        CM_Db_Db::delete('cm_languageValue', ['languageKeyId' => $this->getId()]);
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
     * @throws CM_Exception_Invalid
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
            'nameHash'                => self::calculateHash($name),
        ]);
        try {
            $languageKey->commit();
        } catch (CM_Db_Exception $e) {
            $languageKey = self::findByName($name);
            if (null === $languageKey) {
                throw new CM_Exception_Invalid('Unable to create new language key', $e->getSeverity(), $e->getMetaInfo());
            }
        }

        return $languageKey;
    }

    /**
     * @param string $name
     * @return CM_Model_LanguageKey|null
     */
    public static function findByName($name) {
        $name = (string) $name;
        $languageKeyId = CM_Db_Db::select('cm_model_languagekey', 'id', ['name' => $name], 'id ASC')->fetchColumn();
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
        return (boolean) CM_Db_Db::count('cm_model_languagekey', ['name' => $name]);
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
     * @param string $name
     * @return string
     */
    public static function calculateHash($name) {
        return hash('sha1', (string) $name);
    }
}
