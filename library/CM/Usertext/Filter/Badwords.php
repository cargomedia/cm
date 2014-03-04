<?php

class CM_Usertext_Filter_Badwords implements CM_Usertext_Filter_Interface {

  public function transform($text, CM_Render $render) {
    $text = (string) $text;
    $badwordList = new CM_Paging_ContentList_Badwords;
    $text = $badwordList->replaceMatch($text, '…');

    return $text;
  }
}
