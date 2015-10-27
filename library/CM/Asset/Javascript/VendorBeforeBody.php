<?php

class CM_Asset_Javascript_VendorBeforeBody extends CM_Asset_Javascript_Vendor {

    protected function _getDistPath() {
        return 'client-vendor/before-body/';
    }

    protected function _getSourcePath() {
        return 'client-vendor/before-body-source/';
    }
}
