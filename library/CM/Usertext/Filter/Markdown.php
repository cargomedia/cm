<?php

class CM_Usertext_Filter_Markdown extends CM_Usertext_Filter_Abstract {

    /** @var bool $_skipAnchors */
    private $_skipAnchors;

    /** @var bool $_imgLazy */
    private $_imgLazy;

    /**
     * @param bool|null $skipAnchors
     * @param bool|null $imgLazy
     */
    function __construct($skipAnchors = null, $imgLazy = null) {
        $this->_skipAnchors = (boolean) $skipAnchors;
        $this->_imgLazy = (boolean) $imgLazy;
    }

    public function getCacheKey() {
        return parent::getCacheKey() + array('_skipAnchors' => $this->_skipAnchors, '_imgLazy' => $this->_imgLazy);
    }

    public function transform($text, CM_Frontend_Render $render) {
        $text = (string) $text;
        $markdownParser = new CM_Usertext_Markdown($this->_skipAnchors, $this->_imgLazy);
        return $markdownParser->transform($text);
    }
}
