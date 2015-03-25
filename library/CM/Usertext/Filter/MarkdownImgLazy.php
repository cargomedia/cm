<?php

class CM_Usertext_Filter_MarkdownImgLazy extends CM_Usertext_Filter_Markdown {

    public function transform($text, CM_Frontend_Render $render) {
        $text = (string) $text;
        $markdownParser = new CM_Usertext_MarkdownImgLazy($this->_skipAnchors);
        return $markdownParser->transform($text);
    }
}
