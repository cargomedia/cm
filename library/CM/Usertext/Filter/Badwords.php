<?php

class CM_Usertext_Filter_Badwords extends CM_Usertext_Filter_ListReplace {

    /**
     * @param string|null $replacementPattern
     */
    public function __construct($replacementPattern = null) {
        $replacementPattern = null !== $replacementPattern ? (string) $replacementPattern : '…';
        parent::__construct(new CM_Paging_ContentList_Badwords(), $replacementPattern);
    }
}
