<?php

class CM_Usertext_Filter_Emoticon_UnescapeMarkdown extends CM_Usertext_Filter_Abstract {

    public function transform($text, CM_Frontend_Render $render) {
        $text = (string) $text;
        $text = preg_replace_callback('#:[[:alnum:]-]{1,50}:#u', function ($matches) {
            return str_replace('-', '_', $matches[0]);
        }, $text);
        return $text;
    }
}
