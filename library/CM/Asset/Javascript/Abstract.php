<?php

abstract class CM_Asset_Javascript_Abstract extends CM_Asset_Abstract {

    /** @var CM_Site_Abstract */
    protected $_site;

    /**
     * @param CM_Site_Abstract $site
     */
    public function __construct(CM_Site_Abstract $site) {
        $this->_site = $site;
    }

    /**
     * @param string      $moduleName
     * @param string|null $path
     * @return string
     */
    protected function _getPathInModule($moduleName, $path = null) {
        $path = null !== $path ? (string) $path : '';
        return DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . $path;
    }

    /**
     * @return CM_Site_Abstract
     */
    public function getSite() {
        return $this->_site;
    }
}
