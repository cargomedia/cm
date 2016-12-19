<?php

class CM_Site_SiteSettings extends CM_Model_Abstract {

    /**
     * @return int|null
     */
    public function getSiteId() {
        return $this->_get('siteId');
    }

    /**
     * @param int $siteId
     */
    public function setSiteId($siteId) {
        return $this->_set('siteId', $siteId);
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
        if (!is_numeric($value) && CM_Util::jsonIsValid($value)) { //big integers are valid JSON but can not be decoded properly
            $value = CM_Util::jsonDecode($value);
        }
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
        return CM_Site_Abstract::findClassName($this->getSiteId());
    }

    protected function _getSchema() {
        return new CM_Model_Schema_Definition([
            'siteId'        => ['type' => 'int', 'optional' => true],
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
     * @param int|null       $siteId
     * @param string         $name
     * @param CM_Params|null $configuration
     * @return CM_Site_SiteSettings
     */
    public static function create($siteId = null, $name, CM_Params $configuration = null) {
        if (null !== $siteId) {
            $siteId = (int) $siteId;
        }
        $name = (string) $name;
        if (null === $configuration) {
            $configuration = CM_Params::factory([]);
        }
        $siteSettings = new self();
        $siteSettings->_set([
            'siteId'        => $siteId,
            'name'          => $name,
            'configuration' => CM_Util::jsonEncode($configuration->getParamsEncoded()),
        ]);
        $siteSettings->commit();
        return $siteSettings;
    }

    /**
     * @param int $siteId
     * @return CM_Site_SiteSettings|null
     */
    public static function findBySiteId($siteId) {
        /** @var CM_Model_StorageAdapter_Database $adapter */
        $adapter = self::_getStorageAdapter(self::getPersistenceClass());
        $id = $adapter->findByData(self::getTypeStatic(), ['siteId' => (int) $siteId]);
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
