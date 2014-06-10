<?php

abstract class CM_Usertext_Filter_Abstract implements CM_Usertext_Filter_Interface {

    public function getCacheKey() {
        return array('filter' => get_called_class());
    }

    abstract public function transform($text, CM_Frontend_Render $render);
}
