<?php

class CM_Asset_Javascript_Bundle_Vendor_BeforeBody extends CM_Asset_Javascript_Bundle_Vendor_Abstract {

    protected function _getBundleName() {
        return 'before-body.js';
    }

    protected function _getDistPath() {
        return 'client-vendor/before-body/';
    }

    protected function _getMainPath() {
        return 'client-vendor/before-body-main/';
    }
}
