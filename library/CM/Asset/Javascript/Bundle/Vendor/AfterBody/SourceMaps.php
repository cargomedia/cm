<?php

class CM_Asset_Javascript_Bundle_Vendor_AfterBody_SourceMaps extends CM_Asset_Javascript_Bundle_Vendor_AfterBody {

    public function get() {
        return $this->getSourceMaps(!$this->_isDebug());
    }
}
