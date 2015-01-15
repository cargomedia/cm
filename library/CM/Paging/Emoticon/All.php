<?php

class CM_Paging_Emoticon_All extends CM_Paging_Emoticon_Abstract {

    public function __construct() {
        $source = new CM_PagingSource_Array(CM_Emoticon::getEmoticonData());
        parent::__construct($source);
    }
}
