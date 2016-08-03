<?php

abstract class CM_Asset_Javascript_Bundle_Vendor_Abstract extends CM_Asset_Javascript_Bundle_Abstract {

    /**
     * @param CM_Site_Abstract $site
     * @param bool|null        $debug
     * @param string|null      $type
     */
    public function __construct(CM_Site_Abstract $site, $debug = null, $type = null) {
        parent::__construct($site, $debug);
        $type = $type ? (string) $type : null;
        $this->_process($type);
    }

    /**
     * @param string|null $type
     */
    protected function _process($type) {
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
