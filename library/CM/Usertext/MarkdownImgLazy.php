<?php

class CM_Usertext_MarkdownImgLazy extends CM_Usertext_Markdown {

    function _doImages_reference_callback($matches) {
        $key = parent::_doImages_reference_callback($matches);
        $this->html_hashes[$key] = $this->addLazyAttrs($this->html_hashes[$key]);
        return $key;
    }

    function _doImages_inline_callback($matches) {
        $key = parent::_doImages_inline_callback($matches);
        $this->html_hashes[$key] = $this->addLazyAttrs($this->html_hashes[$key]);
        return $key;
    }

    function addLazyAttrs($tagText) {
        $tagText = str_replace('>', ' class="lazy">', $tagText);
        return str_replace('src=', 'data-original=', $tagText);
    }
}
