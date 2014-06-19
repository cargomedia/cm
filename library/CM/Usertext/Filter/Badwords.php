<?php

class CM_Usertext_Filter_Badwords extends CM_Usertext_Filter_Abstract {

    public function transform($text, CM_Frontend_Render $render) {
        $text = (string) $text;
        $badwordList = new CM_Paging_ContentList_Badwords;
        $text = $badwordList->replaceMatch($text, '…');

        return $text;
    }
}
