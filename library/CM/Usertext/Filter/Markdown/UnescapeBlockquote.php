<?php

class CM_Usertext_Filter_Markdown_UnescapeBlockquote extends CM_Usertext_Filter_Abstract {

    public function transform($text, CM_Frontend_Render $render) {
        $text = (string) $text;
        $text = preg_replace('#^(\s*)&gt;(\s)#m', '$1>$2', $text);
        return $text;
    }
}
