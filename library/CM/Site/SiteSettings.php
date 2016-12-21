<?php

class CM_Site_SiteSettings extends CM_Model_Abstract {

    /**
     * @return int|null
     */
    public function getSiteType() {
        return $this->_get('siteType');
    }

    /**
     * @param int $siteType
     */
    public function setSiteType($siteType) {
        return $this->_set('siteType', $siteType);
    }

    /**
     * @return CM_Params
     */
    public function getConfiguration() {
        if (!$this->_has('configuration')) {
            return CM_Params::factory([]);
        }
        $paramsEncoded = CM_Util::jsonDecode($this->_get('configuration'));
        return CM_Params::factory($paramsEncoded, true);
    }

    /**
     * @param CM_Params $configuration
     */
    public function setConfiguration(CM_Params $configuration) {
        $this->_set('configuration', CM_Util::jsonEncode($configuration->getParamsEncoded()));
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function upsertConfigurationValue($key, $value) {
        $key = (string) $key;
        $value = (string) $value;
        $configurationMap = $this->getConfiguration()->getParamsDecoded();
        $configurationMap[$key] = $value;
        $this->setConfiguration(CM_Params::factory($configurationMap));
    }

    /**
     * @return int
     */
    public function getConfigurationSize() {
        return count($this->getConfiguration()->getParamNames());
    }

    /**
     * @return string|null
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

    /**
     * @return string|null
     */
    public function findSiteClassName() {
        return CM_Site_Abstract::findClassName($this->getSiteType());
    }

    protected function _getSchema() {
        return new CM_Model_Schema_Definition([
            'siteType'      => ['type' => 'int', 'optional' => true],
            'name'          => ['type' => 'string'],
            'configuration' => ['type' => 'string'],
        ]);
    }

    protected function _getContainingCacheables() {
        $cacheables = parent::_getContainingCacheables();
        $cacheables[] = new CM_Paging_SiteSettings_All();
        return $cacheables;
    }

    /**
     * @param int|null       $siteType
     * @param string         $name
     * @param CM_Params|null $configuration
     * @return CM_Site_SiteSettings
     */
    public static function create($siteType = null, $name, CM_Params $configuration = null) {
        if (null !== $siteType) {
            $siteType = (int) $siteType;
        }
        $name = (string) $name;
        if (null === $configuration) {
            $configuration = CM_Params::factory([]);
        }
        $siteSettings = new self();
        $siteSettings->_set([
            'siteType'      => $siteType,
            'name'          => $name,
            'configuration' => CM_Util::jsonEncode($configuration->getParamsEncoded()),
        ]);
        $siteSettings->commit();
        return $siteSettings;
    }

    /**
     * @param int $siteType
     * @return CM_Site_SiteSettings|null
     */
    public static function findBySiteType($siteType) {
        /** @var CM_Model_StorageAdapter_Database $adapter */
        $adapter = self::_getStorageAdapter(self::getPersistenceClass());
        $id = $adapter->findByData(self::getTypeStatic(), ['siteType' => (int) $siteType]);
        if (null === $id) {
            return null;
        }
        return new self($id['id']);
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_Database';
    }

    public static function getTableName() {
        return 'cm_site_settings';
    }
}
