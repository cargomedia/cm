<?php

class CM_Paging_Example_Todo extends CM_Paging_Abstract {

    public function __construct() {
        $source = new CM_PagingSource_Sql('`id`', 'cm_model_example_todo');
        parent::__construct($source);
    }

    protected function _processItem($itemId) {
        return new CM_Model_Example_Todo($itemId);
    }
}
