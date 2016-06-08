<?php

class CM_Paging_Example_Todo extends CM_Paging_Abstract implements CM_ArrayConvertible, CM_Typed {

    public function __construct() {
        $source = new CM_PagingSource_Sql('`id`', 'cm_model_example_todo');
        parent::__construct($source);
    }

    public function toArrayIdOnly() {
        return array('_type' => $this->getType());
    }

    public function toArray() {
        $array = $this->toArrayIdOnly();
        $array['items'] = $this->getItems();
        return $array;
    }

    protected function _processItem($itemId) {
        return new CM_Model_Example_Todo($itemId);
    }

    public static function fromArray(array $array) {
        throw new CM_Exception('Not supported.');
    }
}
