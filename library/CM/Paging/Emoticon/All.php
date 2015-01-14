<?php

class CM_Paging_Emoticon_All extends CM_Paging_Emoticon_Abstract {

    public function __construct() {
        $emoticons = CM_Emoticon::getEmoticonData();
        $source = new CM_PagingSource_Array(\Functional\pluck($emoticons, 'name'));
        parent::__construct($source);
    }
}
