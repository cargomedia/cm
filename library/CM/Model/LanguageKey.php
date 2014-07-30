<?php

class CM_Model_LanguageKey extends CM_Model_Abstract {

    const MAX_UPDATE_COUNT = 50;

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
            if ($this->_getUpdateCount() > self::MAX_UPDATE_COUNT) {
                $message = [
                    'Variables for languageKey `' . $this->_get('name') . '` have been updated over ' . self::MAX_UPDATE_COUNT .
                    ' times since release.',
                    'Previous variables: `' . CM_Util::var_line($previousVariables) . '`',
                    'Current variables: `' . CM_Util::var_line($variables) . '`',
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
        return CM_Params::jsonDecode($variablesEncoded);
    }

    /**
     * @return int
     */
    protected function _getUpdateCount() {
        $deployVersion = CM_App::getInstance()->getDeployVersion();
        if ($deployVersion > $this->_get('updateCountResetVersion')) {
            return 0;
        }
        return $this->_get('updateCount');
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
            'updateCount'             => array('type' => 'int'),
            'javascript'              => array('type' => 'int'),
        ));
    }

    protected function _increaseUpdateCount() {
        $data = [
            'updateCount' => $this->_getUpdateCount() + 1
        ];
        if (CM_App::getInstance()->getDeployVersion() > $this->_get('updateCountResetVersion')) {
            $data['updateCountResetVersion'] = CM_App::getInstance()->getDeployVersion();
        }
        $this->_set($data);
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
        $languageKey->_set('updateCount', 0);
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
        $languageKeyIdList = CM_Db_Db::select('cm_model_languagekey', 'id', array('name' => $name), 'id ASC')->fetchAllColumn();
        if (count($languageKeyIdList) === 0) {
            return null;
        }
        $languageKeyId = array_shift($languageKeyIdList);
        if (count($languageKeyIdList) > 0) {
            CM_Db_Db::exec("DELETE FROM `cm_model_languagekey` WHERE `name` = ? AND `id` != ?", array($name, $languageKeyId));
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
