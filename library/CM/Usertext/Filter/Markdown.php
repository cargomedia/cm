<?php

class CM_Usertext_Filter_Markdown extends CM_Usertext_Filter_Abstract {

    /** @var bool $_skipAnchors */
    private $_skipAnchors;

    /**
     * @param bool|null $skipAnchors
     */
    function __construct($skipAnchors = null) {
        $this->_skipAnchors = (boolean) $skipAnchors;
    }

    public function getCacheKey() {
        return parent::getCacheKey() + array('_skipAnchors' => $this->_skipAnchors);
    }

    public function transform($text, CM_Frontend_Render $render) {
        $text = (string) $text;
        $markdownParser = new CM_Usertext_Markdown($this->_skipAnchors);
        return $markdownParser->transform($text);
    }
}
