<?php

interface CM_Usertext_Filter_Interface {

    /**
     * @return array
     */
    public function getCacheKey();

    /**
     * @param string             $text
     * @param CM_Frontend_Render $render
     * @return string
     */
    public function transform($text, CM_Frontend_Render $render);
}
