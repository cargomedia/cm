<?php

class CM_Asset_Javascript_Internal extends CM_Asset_Javascript_Abstract {

    /**
     * @param CM_Site_Abstract $site
     * @param bool|null        $debug
     */
    public function __construct(CM_Site_Abstract $site, $debug = null) {
        parent::__construct($site, $debug);

        $this->_js->append('var cm = new ' . $this->_getAppClassName($site) . '();');
        $this->_js->append((new CM_File(DIR_ROOT . 'resources/config/js/internal.js'))->read());
    }

    /**
     * @param CM_Site_Abstract $site
     * @return string
     * @throws CM_Exception_Invalid
     */
    private function _getAppClassName(CM_Site_Abstract $site) {
        foreach ($site->getModules() as $moduleName) {
            $file = new CM_File(DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . 'library/' . $moduleName . '/App.js');
            if ($file->exists()) {
                return $moduleName . '_App';
            }
        }
        throw new CM_Exception_Invalid('No App class found');
    }
}
