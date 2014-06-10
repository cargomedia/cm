<?php

class CM_Usertext_Filter_MaxLength extends CM_Usertext_Filter_Abstract {

    /** @var int|null */
    private $_lengthMax = null;

    /**
     * @param int|null $lengthMax
     */
    function __construct($lengthMax = null) {
        if (null !== $lengthMax) {
            $this->_lengthMax = (int) $lengthMax;
        }
    }

    public function getCacheKey() {
        return array_merge(parent::getCacheKey(), array('_lengthMax' => $this->_lengthMax));
    }

    public function transform($text, CM_Frontend_Render $render) {
        $text = (string) $text;
        if (null === $this->_lengthMax) {
            return $text;
        }
        if (strlen($text) > $this->_lengthMax) {
            $text = mb_substr($text, 0, $this->_lengthMax);
            $lastBlank = mb_strrpos($text, ' ');
            if ($lastBlank > 0) {
                $text = mb_substr($text, 0, $lastBlank);
            }
            $text = $text . '…';
        }
        return $text;
    }
}
