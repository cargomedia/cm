<?php

class CM_Asset_Javascript_VendorAfterBody extends CM_Asset_Javascript_Vendor {

    protected function _getDistPath() {
        return 'client-vendor/after-body/';
    }

    protected function _getSourcePath() {
        return 'client-vendor/after-body-source/';
    }
}
