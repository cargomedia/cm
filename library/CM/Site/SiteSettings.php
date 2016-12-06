<?php

class CM_Site_SiteSettings extends CM_Model_Abstract {

    /**
     * @return int
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
            return CM_Params::factory();
        }
        $paramsEncoded = CM_Params::jsonDecode($this->_get('configuration'));
        return CM_Params::factory($paramsEncoded, true);
    }

    /**
     * @param CM_Params $configuration
     */
    public function setConfiguration(CM_Params $configuration) {
        $this->_set('configuration', CM_Params::jsonEncode($configuration->getParamsEncoded()));
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

    /**
     * @return null|string
     */
    public function findSiteClassName() {
        return CM_Site_Abstract::findClassName($this->getSiteId());
    }

    protected function _getSchema() {
        return new CM_Model_Schema_Definition([
            'siteId'        => ['type' => 'int'],
            'configuration' => ['type' => 'string'],
            'name'          => ['type' => 'string'],
        ]);
    }

    protected function _getContainingCacheables() {
        $cacheables = parent::_getContainingCacheables();
        $cacheables[] = new CM_Paging_SiteSettings_All();
        return $cacheables;
    }

    /**
     * @param int       $siteId
     * @param CM_Params $configuration
     * @param string    $name
     * @return CM_Site_SiteSettings
     */
    public static function create($siteId, CM_Params $configuration, $name) {
        $siteSettings = new self();
        $siteSettings->_set([
            'siteId'        => (int) $siteId,
            'configuration' => CM_Params::jsonEncode($configuration->getParamsEncoded()),
            'name'          => (string) $name,
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
