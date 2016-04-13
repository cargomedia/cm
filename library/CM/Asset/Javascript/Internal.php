<?php

class CM_Asset_Javascript_Internal extends CM_Asset_Javascript_Abstract {

    /**
     * @param CM_Site_Abstract $site
     */
    public function __construct(CM_Site_Abstract $site) {
        $this->_content = 'var CM_Exception = require("CM_Exception");';
        $this->_content .= 'var CM_Exception_AuthRequired = require("CM_Exception_AuthRequired");' . PHP_EOL;
        $this->_content .= 'var CM_Exception_FormFieldValidation = require("CM_Exception_FormFieldValidation");' . PHP_EOL;
        $this->_content .= 'var CM_Exception_Invalid = require("CM_Exception_Invalid");' . PHP_EOL;
        $this->_content .= 'var CM_Exception_Nonexistent = require("CM_Exception_Nonexistent");' . PHP_EOL;
        $this->_content .= 'var CM_Exception_RequestFailed = require("CM_Exception_RequestFailed");' . PHP_EOL;
        $this->_content .= 'var App = require("' . $this->_getAppClassName($site) . '");' . PHP_EOL;
        $this->_content .= 'var cm = new App();' . PHP_EOL;
        $this->_content .= (new CM_File(DIR_ROOT . 'resources/config/js/internal.js'))->read();
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
