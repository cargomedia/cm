<?php

abstract class CM_Asset_Javascript_Abstract extends CM_Asset_Abstract {

    /** @var CM_Site_Abstract */
    protected $_site;

    /** @var bool */
    protected $_debug;

    /**
     * @param CM_Site_Abstract $site
     * @param bool|null        $debug
     */
    public function __construct(CM_Site_Abstract $site, $debug = null) {
        $this->_site = $site;
        $this->_debug = (bool) $debug;
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

    /**
     * @return bool
     */
    protected function _isDebug() {
        return $this->_debug;
    }
}
