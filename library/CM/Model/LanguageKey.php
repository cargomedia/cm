<?php

class CM_Model_LanguageKey extends CM_Model_Abstract {

    /**
     * @param string[]|null $variables
     * @throws CM_Exception
     * @throws CM_Exception_Invalid
     */
    public function setVariables(array $variables = null) {
        $previousVariables = array();
        if ($this->_has('variables')) {
            $previousVariables = $this->getVariables();
        }
        $variables = (array) $variables;
        if ($previousVariables !== $variables) {
            $variablesEncoded = json_encode($variables);
            $this->_set('variables', $variablesEncoded);

            $this->_increaseUpdateCount();
            if ($this->_has('updateCount') && $this->_get('updateCount') > 50) {
                $message = [
                    'Variables for languageKey `' . $this->_get('name') . '` have been updated over 50 times since release.',
                    'Previous variables: `' . var_export($previousVariables, true) . '`',
                    'Current variables: `' . var_export($variables, true) . '`',
                ];
                throw new CM_Exception_Invalid(join(PHP_EOL, $message));
            }
        }
    }

    /**
     * @return string[]
     */
    public function getVariables() {
        if (!$this->_has('variables')) {
            return array();
        }
        $variablesEncoded = $this->_get('variables');
        return json_decode($variablesEncoded, true);
    }

    protected function _onChange() {
        /** @var CM_Model_Language $language */
        foreach (new CM_Paging_Language_All() as $language) {
            $language->getTranslations()->_change();
        }
    }

    protected function _getSchema() {
        return new CM_Model_Schema_Definition(array(
            'name'                    => array('type' => 'string'),
            'variables'               => array('type' => 'string', 'optional' => true),
            'updateCountResetVersion' => array('type' => 'int', 'optional' => true),
            'updateCount'             => array('type' => 'int', 'optional' => true),
            'javascript'              => array('type' => 'int'),
        ));
    }

    protected function _increaseUpdateCount() {
        $updateCount = 0;
        if ($this->_has('updateCount')) {
            $updateCount = $this->_get('updateCount');
        }
        $this->_set('updateCount', $updateCount + 1);
    }

    protected function _onDeleteBefore() {
        CM_Db_Db::delete('cm_languageValue', array('languageKeyId' => $this->getId()));
    }

    /**
     * @param string     $name
     * @param array|null $variables
     * @return CM_Model_LanguageKey
     */
    public static function create($name, array $variables = null) {
        $languageKey = new self();
        $languageKey->_set('name', $name);
        $languageKey->setVariables($variables);
        $languageKey->commit();
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
            self::create($name, $variableNames);
            // check if the language Key is double inserted because of high load
            self::clearDuplicates($name);
            $languageKey = self::findByName($name);
        }
        if (null !== $variableNames) {
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
        $languageKey->delete();
    }

    /**
     * @param string $name
     */
    public static function clearDuplicates($name) {
        $name = (string) $name;
        $languageKeyIdList = CM_Db_Db::select('cm_model_languagekey', 'id', array('name' => $name), 'id ASC')->fetchAllColumn();
        if (count($languageKeyIdList) > 1) {
            $languageKeyId = array_shift($languageKeyIdList);
            CM_Db_Db::exec("DELETE FROM `cm_model_languagekey` WHERE `name` = ? AND `id` != ?", array($name, $languageKeyId));
        }
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_Database';
    }
}
