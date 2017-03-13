<?php

abstract class CM_Asset_Javascript_Bundle_Vendor_Abstract extends CM_Asset_Javascript_Bundle_Abstract {

    public function __construct(CM_Site_Abstract $site, $sourceMapsOnly = null) {
        parent::__construct($site, $sourceMapsOnly);
        $this->_process();
    }

    protected function _process() {
        $this->_appendDirectoryGlob($this->_getDistPath(), 'vendor');
        $this->_appendDirectoryBrowserify($this->_getMainPath());
    }

    /**
     * @return string
     */
    abstract protected function _getDistPath();

    /**
     * @return string
     */
    abstract protected function _getMainPath();
}
