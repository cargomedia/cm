<?php

class CM_Usertext_Filter_Badwords extends CM_Usertext_Filter_ListReplace {

    /**
     * @param string|null $replace
     */
    public function __construct($replace = null) {
        $replace = null !== $replace ? (string) $replace : '…';
        parent::__construct(new CM_Paging_ContentList_Badwords(), $replace);
    }
}
