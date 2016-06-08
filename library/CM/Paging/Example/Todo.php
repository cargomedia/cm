<?php

class CM_Paging_Example_Todo extends CM_Paging_Bound {

    public function __construct() {
        $source = new CM_PagingSource_Sql('`id`', 'cm_model_example_todo');
        parent::__construct($source);
    }

    protected function _getStreamChannel() {
        return CM_Model_StreamChannel_Message_Model::create(CM_Model_Example_Todo::getTypeStatic());
    }
}
