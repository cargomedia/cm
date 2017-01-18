<?php

class CM_Paging_SiteSettings_All extends CM_Paging_Abstract {

    public function __construct() {
        $source = new CM_PagingSource_Sql('id', 'cm_site_settings', null, 'id');
        $source->enableCache();
        parent::__construct($source);
    }

    protected function _processItem($item) {
        return new CM_Site_SiteSettings($item);
    }
}
