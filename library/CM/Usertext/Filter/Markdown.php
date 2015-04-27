<?php

class CM_Usertext_Filter_Markdown extends CM_Usertext_Filter_Abstract {

    /** @var bool $_skipAnchors */
    private $_skipAnchors;

    /** @var bool $_imgDimensions */
    private $_imgDimensions;

    /**
     * @param bool|null $skipAnchors
     * @param bool|null $imgDimensions
     */
    function __construct($skipAnchors = null, $imgDimensions = null) {
        $this->_skipAnchors = (boolean) $skipAnchors;
        $this->_imgDimensions = (boolean) $imgDimensions;
    }

    public function getCacheKey() {
        return parent::getCacheKey() + array('_skipAnchors' => $this->_skipAnchors, '_imgDimensions' => $this->_imgDimensions);
    }

    public function transform($text, CM_Frontend_Render $render) {
        $text = (string) $text;
        $markdownParser = new CM_Usertext_Markdown($this->_skipAnchors, $this->_imgDimensions);
        return $markdownParser->transform($text);
    }
}
