<?php

class CM_Asset_Javascript_Bundle_Vendor_AfterBody extends CM_Asset_Javascript_Bundle_Vendor_Abstract {

    public function get() {
        return $this->getCode(!$this->_isDebug());
    }

    protected function _getBundleName() {
        return 'after-body.js';
    }

    protected function _getDistPath() {
        return 'client-vendor/after-body/';
    }

    protected function _getMainPath() {
        return 'client-vendor/after-body-main/';
    }
}
