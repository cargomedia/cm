<?php

class CM_Asset_Javascript_Bundle_Vendor_BeforeBody_SourceMaps extends CM_Asset_Javascript_Bundle_Vendor_BeforeBody {

    public function get() {
        return $this->getSourceMaps(!$this->_isDebug());
    }
}
