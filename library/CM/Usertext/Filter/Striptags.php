<?php

class CM_Usertext_Filter_Striptags extends CM_Usertext_Filter_Abstract {

    /** @var string[] */
    private $_allowedTags;

    /**
     * @param string[]|null $allowedTags
     */
    function __construct($allowedTags = null) {
        $this->_allowedTags = (array) $allowedTags;
    }

    public function getCacheKey() {
        return array_merge(parent::getCacheKey(), array('_allowedTags' => $this->_allowedTags));
    }

    public function transform($text, CM_Frontend_Render $render) {
        $text = (string) $text;
        $allowedTags = '';
        foreach ($this->_allowedTags as $tag) {
            $allowedTags .= '<' . $tag . '>';
        }
        return strip_tags($text, $allowedTags);
    }
}
