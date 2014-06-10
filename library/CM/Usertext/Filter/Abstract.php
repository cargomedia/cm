<?php

class CM_Usertext_Filter_Abstract implements CM_Usertext_Filter_Interface {

    public function getCacheKey() {
        return array('filter' => get_called_class());
    }

    public function transform($text, CM_Frontend_Render $render) {
        $text = (string) $text;
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        return $text;
    }
}
