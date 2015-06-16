<?php

class CM_Paging_Currency_All extends CM_Paging_Currency_Abstract {

    public function __construct() {
        $source = new CM_PagingSource_Sql('id', 'cm_model_currency', null, 'id');
        $source->enableCache();
        parent::__construct($source);
    }
}
