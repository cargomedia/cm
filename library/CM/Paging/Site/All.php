<?php

class CM_Paging_Site_All extends CM_Paging_Abstract {

    public function __construct() {
        $source = new CM_PagingSource_MongoDb('cm_site', [], ['_type' => 1]);
        $source->enableCache();
        parent::__construct($source);
    }

    protected function _processItem($item) {
        return CM_Site_Abstract::factoryFromType($item['_id'], $item['_type']);
    }
}
