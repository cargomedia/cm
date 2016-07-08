<?php

class CM_Paging_List extends CM_Paging_Abstract {

    /**
     * @param CM_PagingSource_Array|array $source
     * @throws CM_Exception_Invalid
     */
    public function __construct($source) {
        if (is_array($source)) {
            $source = new CM_PagingSource_Array($source);
        }
        if (!($source instanceof CM_PagingSource_Array)) {
            throw new CM_Exception_Invalid('CM_Paging_List should be instantiated with either an array or CM_PagingSource_Array instance.');
        }
        parent::__construct($source);
    }
}
