<?php

class CM_Site_SiteSettings extends CM_Model_Abstract {

    /**
     * @return int
     */
    public function getClassType() {
        return $this->_get('classType');
    }

    /**
     * @param int $classType
     */
    public function setClassType($classType) {
        return $this->_set('classType', $classType);
    }

    /**
     * @return CM_Params
     */
    public function getSettings() {
        if (!$this->_has('settings')) {
            return CM_Params::factory();
        }
        $paramsEncoded = CM_Params::jsonDecode($this->_get('settings'));
        return CM_Params::factory($paramsEncoded, true);
    }

    /**
     * @param CM_Params $settings
     */
    public function setSettings(CM_Params $settings) {
        $this->_set('settings', CM_Params::jsonEncode($settings->getParamsEncoded()));
    }

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
        return $this->_set('name', $name);
    }

    protected function _getSchema() {
        return new CM_Model_Schema_Definition([
            'classType' => ['type' => 'int'],
            'settings'  => ['type' => 'string'],
            'name'      => ['type' => 'string'],
        ]);
    }

    /**
     * @param int       $classType
     * @param CM_Params $settings
     * @param string    $name
     * @return CM_Site_SiteSettings
     */
    public static function create($classType, CM_Params $settings, $name) {
        $siteSettings = new self();
        $siteSettings->_set([
            'classType' => (int) $classType,
            'settings'  => CM_Params::jsonEncode($settings->getParamsEncoded()),
            'name'      => (string) $name,
        ]);
        $siteSettings->commit();
        return $siteSettings;
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_Database';
    }

    public static function getTableName() {
        return 'cm_site_settings';
    }
}
