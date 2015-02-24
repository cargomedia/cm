<?php

class CM_Adprovider_Client extends CM_Class_Abstract {

    /** @var array[] */
    private $_zones;

    /** @var bool */
    private $_enabled;

    /** @var CM_Adprovider_Adapter_Abstract[] */
    private $_adapters = array();

    /**
     * @param bool    $enabled
     * @param array[] $zones
     */
    public function __construct($enabled, array $zones) {
        $this->_enabled = (bool) $enabled;
        $this->_zones = $zones;
    }

    /**
     * @param CM_Site_Abstract $site
     * @param string           $zoneName
     * @param string[]|null    $variables
     * @throws CM_Exception_Invalid
     * @return string
     */
    public function getHtml(CM_Site_Abstract $site, $zoneName, $variables = null) {
        $zoneName = (string) $zoneName;
        $variables = (array) $variables;
        if (!$this->_getEnabled()) {
            return '';
        }
        $zoneData = $this->_getZone($site, $zoneName);
        if (!array_key_exists('adapter', $zoneData)) {
            throw new CM_Exception_Invalid('Zone `' . $zoneName . '` has no adapter configured.');
        }
        $adapterClassName = (string) $zoneData['adapter'];
        unset($zoneData['adapter']);
        return (string) $this->_getAdapter($adapterClassName)->getHtml($zoneData, $variables);
    }

    /**
     * @param CM_Site_Abstract $site
     * @param string           $zoneName
     * @return array
     * @throws CM_Exception_Invalid
     */
    protected function _getZone(CM_Site_Abstract $site, $zoneName) {
        $cacheKey = CM_CacheConst::AdproviderZones . '_siteId:' . $site->getId();
        $cache = CM_Cache_Local::getInstance();
        if (false === ($zones = $cache->get($cacheKey))) {
            $zones = $this->_getZones();
            if (isset($site->getConfig()->CM_Adprovider->zones)) {
                $zones = array_merge($zones, $site->getConfig()->CM_Adprovider->zones);
            }
            $cache->set($cacheKey, $zones);
        }
        if (!array_key_exists($zoneName, $zones)) {
            throw new CM_Exception_Invalid('Zone `' . $zoneName . '` not configured.');
        }
        return $zones[$zoneName];
    }

    /**
     * @param CM_Adprovider_Adapter_Abstract $adapter
     */
    public function addAdapter(CM_Adprovider_Adapter_Abstract $adapter) {
        $this->_adapters[get_class($adapter)] = $adapter;
    }

    /**
     * @param string $className
     * @return CM_Adprovider_Adapter_Abstract
     * @throws CM_Exception_Invalid
     */
    protected function _getAdapter($className) {
        if (!array_key_exists($className, $this->_adapters)) {
            throw new CM_Exception_Invalid('Adprovider adapter `' . $className . '` not found');
        }
        return $this->_adapters[$className];
    }

    /**
     * @return bool
     */
    private function _getEnabled() {
        return $this->_enabled;
    }

    /**
     * @return array[]
     */
    protected function _getZones() {
        return $this->_zones;
    }
}
