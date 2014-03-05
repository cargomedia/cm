<?php

class CM_Usertext_Filter_Emoticon_UnescapeMarkdown implements CM_Usertext_Filter_Interface {

  public function transform($text, CM_Render $render) {
    $text = (string) $text;
    $text = preg_replace_callback('#:[[:alnum:]-]{1,50}:#u', function ($matches) {
      return str_replace('-', '_', $matches[0]);
    }, $text);
    return $text;
  }
}
